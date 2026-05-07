<!DOCTYPE html>
<html>
<head>
    <title>Tea & Spice Arcade</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: radial-gradient(circle at center, #0a0a0a, #000);
            color: white;
            overflow-x: hidden;
        }

        /* BACKGROUND */
        .bg-glow {
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(0,255,0,0.15), transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(255,0,0,0.12), transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(0,150,255,0.06), transparent 60%);
            animation: moveGlow 8s infinite alternate ease-in-out;
        }

        @keyframes moveGlow {
            from { transform: scale(1); }
            to { transform: scale(1.2); }
        }

        .wrapper {
            position: relative;
            z-index: 2;
            max-width: 1100px;
            margin: auto;
            padding: 40px 20px;
        }

        h1 {
            text-align: center;
            font-size: 42px;
            text-shadow: 0 0 20px rgba(0,255,0,0.6);
        }

        .subtitle {
            text-align: center;
            color: #aaa;
            margin-bottom: 30px;
        }

        /* BANNER */
        .banner {
            height: 260px;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            padding: 30px;
            margin-bottom: 40px;
            background: linear-gradient(120deg, #001a00, #000, #001a00);
            overflow: hidden;
        }

        .banner-text { z-index: 2; }

        .play-btn {
            padding: 10px 20px;
            background: #00ff88;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        /* 🔥 NEW: Banner animation container */
        #anim-box {
            position: relative;
            width: 300px;
            height: 100%;
        }

        /* center animations inside banner */
        #anim-box .snake-mini,
        #anim-box .tetris-mini,
        #anim-box .pacman-mini {
            top: 90px !important;
            left: 120px !important;
            transform: scale(1.6);
        }

        /* GRID */
        .grid {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
        }

        .card {
            width: 200px;
            height: 240px;
            background: #111;
            border-radius: 12px;
            cursor: pointer;
            border: 1px solid #222;
            position: relative;
            overflow: hidden;
        }

        .card h2 {
            position: absolute;
            bottom: 15px;
            width: 100%;
            text-align: center;
        }

        /* 🎮 HOVER */
        .card, .banner {
            transform-style: preserve-3d;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
        }

        .card:hover, .banner:hover {
            box-shadow: 0 20px 50px rgba(0,255,0,0.35);
        }

        .card::before, .banner::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: radial-gradient(circle at var(--x) var(--y),
                rgba(0,255,0,0.25),
                transparent 40%);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .card:hover::before,
        .banner:hover::before {
            opacity: 1;
        }

        /* 🐍 Snake */
        .snake-mini {
            position: absolute;
            top: 40px;
            left: 30px;
            display: flex;
            gap: 4px;
        }

        .snake-mini div {
            width: 12px;
            height: 12px;
            background: #00ff00;
            animation: snakeWave 0.6s infinite alternate;
        }

        @keyframes snakeWave {
            from { transform: translateY(0); }
            to { transform: translateY(6px); }
        }

        /* 🧱 Tetris */
        .tetris-mini {
            position: absolute;
            top: 40px;
            left: 70px;
            width: 40px;
            height: 40px;
            background: #ffcc00;
            animation: tetrisSpin 2s linear infinite;
        }

        @keyframes tetrisSpin {
            0% { transform: rotate(0) translateY(0); }
            50% { transform: rotate(180deg) translateY(10px); }
            100% { transform: rotate(360deg) translateY(0); }
        }

        /* 👾 Pacman */
        .pacman-mini {
            position: absolute;
            top: 40px;
            left: 70px;
            width: 40px;
            height: 40px;
            background: yellow;
            border-radius: 50%;
            animation: chomp 0.3s infinite alternate;
        }

        @keyframes chomp {
            from {
                clip-path: polygon(0% 0%, 100% 50%, 0% 100%);
            }
            to {
                clip-path: polygon(0% 10%, 100% 50%, 0% 90%);
            }
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #444;
        }

    </style>
</head>

<body>

<div class="bg-glow"></div>

<div class="wrapper">

    <h1>🎮 TEA & SPICE ARCADE</h1>
    <div class="subtitle">Play. Compete. Enjoy.</div>

    <!-- FEATURE -->
    <div class="banner" id="banner">
        <div class="banner-text">
            <h2 id="banner-title"></h2>
            <p id="banner-desc"></p>
            <button class="play-btn" id="banner-btn">Play Now</button>
        </div>

        <div id="anim-box"></div>
    </div>

    <!-- GRID -->
    <div class="grid">

        <div class="card" onclick="go('snake')">
            <div class="snake-mini">
                <div></div><div></div><div></div><div></div>
            </div>
            <h2>🐍 SNAKE</h2>
        </div>

        <div class="card" onclick="go('tetris')">
            <div class="tetris-mini"></div>
            <h2>🧱 TETRIS</h2>
        </div>

        <div class="card" onclick="go('pacman')">
            <div class="pacman-mini"></div>
            <h2>👾 PAC-MAN</h2>
        </div>

    </div>

    <div class="footer">Built with Laravel + Phaser 3</div>

</div>

<script>

function go(game) {
    window.location.href = "/game/" + game;
}

/* 🔊 HOVER SOUND */
function hoverSound(){
    let ctx = new (window.AudioContext || window.webkitAudioContext)();
    let osc = ctx.createOscillator();
    let gain = ctx.createGain();

    osc.frequency.value = 500;
    gain.gain.value = 0.02;

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.start();
    osc.stop(ctx.currentTime + 0.05);
}

/* 🎮 HOVER ENGINE */
document.querySelectorAll(".card, .banner").forEach(el => {

    el.addEventListener("mousemove", e => {

        const rect = el.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const midX = rect.width / 2;
        const midY = rect.height / 2;

        const rotateX = -(y - midY) / 12;
        const rotateY = (x - midX) / 12;

        el.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.04)`;

        el.style.setProperty("--x", x + "px");
        el.style.setProperty("--y", y + "px");
    });

    el.addEventListener("mouseleave", () => {
        el.style.transform = "rotateX(0) rotateY(0) scale(1)";
    });

    el.addEventListener("mouseenter", hoverSound);
});

/* 🔥 FIXED BANNER WITH ANIMATIONS */
const games = [
    { name:"snake", title:"🔥 Snake", desc:"Eat and grow" },
    { name:"tetris", title:"🧱 Tetris", desc:"Rotate and stack" },
    { name:"pacman", title:"👾 Pac-Man", desc:"Chase and escape" }
];

let i = 0;

function updateBanner() {
    let g = games[i];

    document.getElementById("banner-title").innerText = g.title;
    document.getElementById("banner-desc").innerText = g.desc;
    document.getElementById("banner-btn").onclick = () => go(g.name);

    const box = document.getElementById("anim-box");

    if (g.name === "snake") {
        box.innerHTML = `
            <div class="snake-mini">
                <div></div><div></div><div></div><div></div>
            </div>
        `;
    }

    if (g.name === "tetris") {
        box.innerHTML = `<div class="tetris-mini"></div>`;
    }

    if (g.name === "pacman") {
        box.innerHTML = `<div class="pacman-mini"></div>`;
    }

    i = (i + 1) % games.length;
}

setInterval(updateBanner, 3000);
updateBanner();

</script>

</body>
</html>