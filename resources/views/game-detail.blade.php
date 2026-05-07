<!DOCTYPE html>
<html>
<head>
    <title>{{ $name }} | Tea & Spice Arcade</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #0a0a0a;
            color: white;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 40px 20px;
        }

        .hero {
            height: 300px;
            border-radius: 12px;
            background: url('https://images.unsplash.com/photo-1606813907291-d86efa9b94db') center/cover;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 32px;
        }

        .desc {
            color: #aaa;
            margin: 20px 0;
        }

        .play-btn {
            padding: 12px 25px;
            background: #00ff88;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            font-weight: bold;
        }

        .play-btn:hover {
            background: #00cc6a;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="hero"></div>

    <h1>{{ strtoupper($name) }}</h1>

    <div class="desc">
        Play this classic arcade game. Use your keyboard to control and survive as long as possible.
    </div>

    <button class="play-btn" onclick="playGame()">▶ Play Now</button>

</div>

<script>
function playGame(){
    window.location.href = "/play/{{ $name }}";
}
</script>

</body>
</html>