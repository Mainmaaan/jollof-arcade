<!DOCTYPE html>
<html>
<head>
    <title>{{ $name }} Game</title>

    <script src="https://cdn.jsdelivr.net/npm/phaser@3.70.0/dist/phaser.js"></script>

    <style>
        body {
            margin: 0;
            background: #050505;
            color: white;
            text-align: center;
            overflow: hidden;
            font-family: Arial;
        }

        #game-container {
            margin: auto;
            display: block;
        }

        canvas {
            display: block;
            margin: 0 auto;
            border: 2px solid #222;
            box-shadow: 0 0 25px rgba(0,255,0,0.15);
        }

        .top-bar {
            padding: 10px;
            background: #111;
            font-size: 14px;
        }

        a {
            color: yellow;
            text-decoration: none;
        }

        /* ✈️ AIRCRAFT */
        .plane {
            position: fixed;
            top: 50px;
            left: -100px;
            font-size: 20px;
            animation: fly 12s linear infinite;
            opacity: 0.3;
        }

        @keyframes fly {
            from { left: -100px; }
            to { left: 110%; }
        }

    </style>
</head>

<body>

<div class="top-bar">
    <a href="/">⬅ Back Home</a> | Playing: {{ $name }}
</div>

<div style="background:#111;padding:12px;">
    <h2 style="margin:0;color:#00ffcc;">🎮 Jollof Arcade</h2>
    <p style="margin:5px 0;font-size:13px;color:#aaa;">
        Built with Laravel + Phaser 3
    </p>
</div>

<div style="background:#111;padding:10px;color:#00ffcc;font-size:13px;">
🎮 Controls: Use <b>WASD</b> or <b>Arrow Keys</b>
</div>

<div style="background:#111;padding:10px;color:#ffcc00;font-size:13px;">
🚧 Pacman & Tetris are under development. Snake mode is fully playable.
</div>

<div class="plane">✈️</div>

<div id="game-container"></div>

<script>

//////////////////////////////
// 🔊 SAFE AUDIO (FIXED)
//////////////////////////////

let audioCtx;

function initAudio() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
}

function beep(freq, duration) {
    if (!audioCtx) return;

    let osc = audioCtx.createOscillator();
    let gain = audioCtx.createGain();

    osc.frequency.value = freq;
    gain.gain.value = 0.05;

    osc.connect(gain);
    gain.connect(audioCtx.destination);

    osc.start();
    osc.stop(audioCtx.currentTime + duration / 1000);
}

document.addEventListener("click", initAudio, { once: true });

//////////////////////////////
// 🎮 PHASER CONFIG
//////////////////////////////

let gameName = "{{ $name }}";

let config = {
    type: Phaser.AUTO,
    width: 600,
    height: 400,
    parent: "game-container",
    backgroundColor: "#050505",
    scene: { create, update }
};

new Phaser.Game(config);


//////////////////////////////
// GLOBAL
//////////////////////////////

let score = 0;
let scoreText, levelText, powerText;

//////////////////////////////
// 🐍 SNAKE (BOSS SYSTEM FIXED)
//////////////////////////////

let snake, food, powerFood, direction, newDirection, moveTime;
let level, bullets, bulletTimer;
let shieldHP, maxShieldHP;
let powerCount;

// 👾 BOSS
let boss, bossHP, bossMaxHP, bossPhase;

let gridWidth = 30;
let gridHeight = 20;

// 🎨 UI
let shieldBarBg, shieldBarFill;
let bossBarBg, bossBarFill;

// 💥 EFFECTS
let shakeTime = 0;
let hitFlashTime = 0;
let slowMotionTime = 0;

function startSnake(scene) {

    snake = [{x:10,y:10},{x:9,y:10},{x:8,y:10}];

    direction = "RIGHT";
    newDirection = "RIGHT";

    food = spawnFood();
    powerFood = spawnPowerFood();

    level = 1;
    bullets = [];
    bulletTimer = 0;

    shieldHP = 0;
    maxShieldHP = 3;
    powerCount = 0;

    // 👾 boss
    boss = null;
    bossHP = 0;
    bossMaxHP = 12;
    bossPhase = 1;

    score = 0;
    moveTime = 0;

    scene.input.keyboard.on("keydown", e => {

        if((e.code==="ArrowLeft"||e.code==="KeyA") && direction!=="RIGHT") newDirection="LEFT";
        if((e.code==="ArrowRight"||e.code==="KeyD") && direction!=="LEFT") newDirection="RIGHT";
        if((e.code==="ArrowUp"||e.code==="KeyW") && direction!=="DOWN") newDirection="UP";
        if((e.code==="ArrowDown"||e.code==="KeyS") && direction!=="UP") newDirection="DOWN";

    });

    ////////////////////////////
    // HUD
    ////////////////////////////

    scoreText = scene.add.text(10,5,"Score: 0",{fontSize:"14px",fill:"#fff"}).setDepth(1000);
    levelText = scene.add.text(140,5,"Level: 1",{fontSize:"14px",fill:"#fff"}).setDepth(1000);
    powerText = scene.add.text(260,5,"Power: 0",{fontSize:"14px",fill:"#00ccff"}).setDepth(1000);

    shieldBarBg = scene.add.rectangle(400,12,120,10,0x222222).setOrigin(0).setDepth(1000);
    shieldBarFill = scene.add.rectangle(400,12,0,10,0x00ff00).setOrigin(0).setDepth(1000);

    bossBarBg = scene.add.rectangle(180,25,240,8,0x222222).setOrigin(0).setDepth(1000).setVisible(false);
    bossBarFill = scene.add.rectangle(180,25,240,8,0xff0000).setOrigin(0).setDepth(1000).setVisible(false);
}

function updateSnake(time, scene) {

    if(time < moveTime) return;

    let baseSpeed = (level === 1) ? 120 : 100;
    let speed = (time < slowMotionTime) ? baseSpeed + 80 : baseSpeed;

    moveTime = time + speed;

    direction = newDirection;

    let head = {...snake[0]};

    if(direction==="LEFT") head.x--;
    if(direction==="RIGHT") head.x++;
    if(direction==="UP") head.y--;
    if(direction==="DOWN") head.y++;

    ////////////////////////////
    // WALL WRAP
    ////////////////////////////
    if(head.x < 0) head.x = gridWidth - 1;
    if(head.x >= gridWidth) head.x = 0;
    if(head.y < 0) head.y = gridHeight - 1;
    if(head.y >= gridHeight) head.y = 0;

    ////////////////////////////
    // SELF COLLISION
    ////////////////////////////
    for(let part of snake){
        if(part.x === head.x && part.y === head.y){
            return restart();
        }
    }

    snake.unshift(head);

    ////////////////////////////
    // FOOD
    ////////////////////////////
    if(head.x === food.x && head.y === food.y){
        score += 5;
        food = spawnFood();
        beep(600,80);
    }

    ////////////////////////////
    // POWER FOOD
    ////////////////////////////
    else if(head.x === powerFood.x && head.y === powerFood.y){

        shieldHP = maxShieldHP;
        powerCount++;

        powerFood = spawnPowerFood();
        beep(1200,120);
    }

    else {
        snake.pop();
    }

    ////////////////////////////
    // LEVEL SYSTEM
    ////////////////////////////

    if(score >= 30 && level === 1){
        level = 2;
        beep(1000,150);
        setTimeout(()=>beep(1300,150),150);
    }

    // 🔥 LOWERED ENTRY → boss comes earlier
    if(score >= 45 && level === 2){
        level = 3;

        bullets = []; // 🧹 FIX: remove old bullets

        boss = { x: 0, y: 0 };
        bossHP = bossMaxHP;
        bossPhase = 1;

        bossBarBg.setVisible(true);
        bossBarFill.setVisible(true);

        beep(200,200);
        setTimeout(()=>beep(400,200),200);
    }

    ////////////////////////////
    // LEVEL 2 BULLETS
    ////////////////////////////
    if(level === 2){

        if(time > bulletTimer){
            bulletTimer = time + 1400;

            bullets.push({
                x: Phaser.Math.Between(0, gridWidth-1),
                y: -1,
                speed: Phaser.Math.FloatBetween(0.4,0.7)
            });
        }

        bullets.forEach(b => {

            b.y += b.speed;

            if(b.x === head.x && Math.floor(b.y) === head.y){

                if(shieldHP > 0){
                    shieldHP--;
                    triggerHitFX(time);
                    b.y = gridHeight+1;
                } else return restart();
            }
        });

        bullets = bullets.filter(b => b.y < gridHeight);
    }

    ////////////////////////////
    // 👾 BOSS SYSTEM (2 PHASES)
    ////////////////////////////
    if(level === 3 && boss){

        // 🟢 PHASE 1 (EASY)
        let bossSpeed = (bossPhase === 1) ? 0.4 : 0.8;

        if(Math.random() < bossSpeed){
            if(boss.x < head.x) boss.x++;
            else if(boss.x > head.x) boss.x--;

            if(boss.y < head.y) boss.y++;
            else if(boss.y > head.y) boss.y--;
        }

        // collision
        if(boss.x === head.x && boss.y === head.y){

            if(shieldHP > 0){
                shieldHP--;
                bossHP--;
                triggerHitFX(time);
            } else return restart();
        }

        // 🔴 PHASE 2 TRIGGER
        if(bossHP <= bossMaxHP / 2 && bossPhase === 1){
            bossPhase = 2;

            // harder: bring bullets back
            bulletTimer = 0;
        }

        // 🔴 PHASE 2 (HARD)
        if(bossPhase === 2){

            if(time > bulletTimer){
                bulletTimer = time + 900;

                bullets.push({
                    x: Phaser.Math.Between(0, gridWidth-1),
                    y: -1,
                    speed: Phaser.Math.FloatBetween(0.6,1.0)
                });
            }
        }

        // WIN
        if(bossHP <= 0){
            alert("🏆 BOSS DEFEATED!");
            return restart();
        }
    }

    ////////////////////////////
    // SHAKE
    ////////////////////////////
    let offsetX = 0, offsetY = 0;

    if(time < shakeTime){
        offsetX = Phaser.Math.Between(-4,4);
        offsetY = Phaser.Math.Between(-4,4);
    }

    ////////////////////////////
    // DRAW
    ////////////////////////////

    scene.children.list
        .filter(obj =>
            obj !== scoreText &&
            obj !== levelText &&
            obj !== powerText &&
            obj !== shieldBarBg &&
            obj !== shieldBarFill &&
            obj !== bossBarBg &&
            obj !== bossBarFill
        )
        .forEach(obj => obj.destroy());

    snake.forEach((p,i)=>{
        let rect = scene.add.rectangle(
            p.x*20+offsetX,
            p.y*20+offsetY,
            20,20,
            i===0?0x00ff00:0x007700
        ).setOrigin(0);

        if(shieldHP>0) rect.setStrokeStyle(2,0x00ffff);
    });

    scene.add.rectangle(food.x*20+offsetX,food.y*20+offsetY,20,20,0xff0000).setOrigin(0);

    let pulse = Math.sin(time*0.01)*3;
    scene.add.rectangle(
        powerFood.x*20+offsetX-pulse/2,
        powerFood.y*20+offsetY-pulse/2,
        20+pulse,
        20+pulse,
        0x00ccff
    ).setOrigin(0);

    bullets.forEach(b=>{
        scene.add.rectangle(b.x*20+offsetX,b.y*20+offsetY,20,20,0xff4444).setOrigin(0);
    });

    if(level === 3 && boss){
        scene.add.rectangle(
            boss.x*20+offsetX,
            boss.y*20+offsetY,
            20,20,
            bossPhase === 1 ? 0xff00ff : 0xff0000
        ).setOrigin(0);
    }

    ////////////////////////////
    // HUD
    ////////////////////////////

    scoreText.setText("Score: "+score);
    levelText.setText("Level: "+level);
    powerText.setText("Power: "+powerCount);

    shieldBarFill.width = (shieldHP/maxShieldHP)*120;

    if(level === 3){
        bossBarFill.width = (bossHP/bossMaxHP)*240;
    }

    ////////////////////////////
    // HIT FLASH
    ////////////////////////////
    if(time < hitFlashTime){
        scene.add.rectangle(0,0,600,400,0xffffff)
            .setOrigin(0)
            .setAlpha(0.2)
            .setDepth(999);
    }
}

function triggerHitFX(time){
    shakeTime = time + 150;
    hitFlashTime = time + 120;
    slowMotionTime = time + 200;
    beep(200,60);
}

function spawnFood(){
    let pos;
    do{
        pos={x:Phaser.Math.Between(0,gridWidth-1),y:Phaser.Math.Between(0,gridHeight-1)};
    }while(snake.some(s=>s.x===pos.x&&s.y===pos.y));
    return pos;
}

function spawnPowerFood(){
    return spawnFood();
}

  

//////////////////////////////
// 🧱 TETRIS (IMPROVED)
//////////////////////////////

let grid, piece;

function startTetris(scene){
    grid = Array(20).fill().map(()=>Array(12).fill(0));
    spawnPiece();

    scene.input.keyboard.on("keydown", e=>{
        if(e.code==="ArrowLeft"||e.code==="KeyA") piece.x--;
        if(e.code==="ArrowRight"||e.code==="KeyD") piece.x++;
        if(e.code==="ArrowDown"||e.code==="KeyS") piece.y++;
        if(e.code==="ArrowUp"||e.code==="KeyW") rotatePiece();
    });
}

function spawnPiece(){
    piece = {
        shape:[[1,1],[1,1]],
        x:5,y:0
    };
}

function rotatePiece(){
    piece.shape = piece.shape[0].map((_,i)=>piece.shape.map(r=>r[i]).reverse());
}

function updateTetris(time, scene){
    piece.y++;

    scene.children.removeAll();

    piece.shape.forEach((row,y)=>{
        row.forEach((val,x)=>{
            if(val){
                scene.add.rectangle((piece.x+x)*20,(piece.y+y)*20,20,20,0xffcc00).setOrigin(0);
            }
        });
    });
}

//////////////////////////////
// 👾 PACMAN (FIXED)
//////////////////////////////

let pacman, mouthOpen = true;

function startPacman(scene){
    pacman = {x:5,y:5};

    scene.input.keyboard.on("keydown", e=>{
        if(e.code==="ArrowLeft"||e.code==="KeyA") pacman.x--;
        if(e.code==="ArrowRight"||e.code==="KeyD") pacman.x++;
        if(e.code==="ArrowUp"||e.code==="KeyW") pacman.y--;
        if(e.code==="ArrowDown"||e.code==="KeyS") pacman.y++;
    });
}

function updatePacman(scene){
    scene.children.removeAll();

    mouthOpen = !mouthOpen;

    let angle = mouthOpen ? 0.25 : 0.05;

    scene.add.arc(
        pacman.x*20+10,
        pacman.y*20+10,
        10,
        Phaser.Math.DegToRad(angle*360),
        Phaser.Math.DegToRad((1-angle)*360),
        false,
        0xffff00
    );
}

//////////////////////////////
// CREATE + UPDATE
//////////////////////////////

function create(){
    if(gameName==="snake") startSnake(this);
    if(gameName==="tetris") startTetris(this);
    if(gameName==="pacman") startPacman(this);
}

function update(time){
    if(gameName==="snake") updateSnake(time,this);
    if(gameName==="tetris") updateTetris(time,this);
    if(gameName==="pacman") updatePacman(this);
}

</script>

</body>
</html>