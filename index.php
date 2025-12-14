<?php
// --- BACKEND: VIEW COUNTER ---
$stats_file = 'views.json';

$default_data = ['views' => 0, 'ips' => []];
if (!file_exists($stats_file)) {
    file_put_contents($stats_file, json_encode($default_data));
}

$json_content = file_get_contents($stats_file);
$data = json_decode($json_content, true);

if (!is_array($data) || !isset($data['views']) || !isset($data['ips'])) {
    $data = $default_data;
}

$user_ip = $_SERVER['REMOTE_ADDR'];
$ip_hash = md5($user_ip);

if (!in_array($ip_hash, $data['ips'])) {
    $data['views']++;
    $data['ips'][] = $ip_hash;
    file_put_contents($stats_file, json_encode($data), LOCK_EX);
}

$views = $data['views'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@Rence141 // Portfolio</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- VARIABLES --- */
        :root {
            --primary: #ffffff;
            --accent: #a8b2d1; 
            --glass-bg: rgba(20, 20, 20, 0.65);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            background-color: #050505;
            color: var(--primary);
            font-family: 'JetBrains Mono', monospace;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: none; 
        }

        /* --- BACKGROUND LAYER --- */
        .bg-layer {
            position: fixed; inset: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -2; 
            opacity: 0; 
            /* UPDATED: Faster transition (0.5s instead of 1s) */
            transition: opacity 0.5s ease-in-out;
            filter: brightness(0.6) contrast(1.1);
        }
        .bg-layer.active { opacity: 1; }

        /* --- RAIN DROPLETS --- */
        .rain-window {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: 10; pointer-events: none; display: none;
        }
        .rain-window.active { display: block; }

        .droplet {
            position: absolute;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 50% 50% 45% 45%; 
            box-shadow: 
                inset 1px 1px 2px rgba(255,255,255,0.3), 
                inset -1px -1px 2px rgba(0,0,0,0.5),   
                1px 1px 2px rgba(0,0,0,0.3);           
            backdrop-filter: blur(2px) brightness(1.2);
        }

        /* --- LEAF EFFECT --- */
        .leaf {
            position: fixed; top: -10vh; z-index: -1; pointer-events: none;
            width: 30px; height: 30px;
            background-size: contain; background-repeat: no-repeat;
            opacity: 0.8;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffffff'%3E%3Cpath d='M17,8C8,10,5.9,16.17,3.82,21.34L5.71,22l1-2.3A4.49,4.49,0,0,0,8,20C19,20,22,3,22,3,21,5,14,5.25,9,6.25S2,11.5,2,13.5a6.22,6.22,0,0,0,1.75,3.75C7,8,17,8,17,8Z'/%3E%3C/svg%3E");
        }
        .leaf.orange { filter: sepia(1) saturate(5) hue-rotate(-30deg); }
        .leaf.green { filter: sepia(1) saturate(3) hue-rotate(70deg); }

        @keyframes fall {
            0% { transform: translate(0, 0) rotate(0deg); opacity: 0; }
            20% { opacity: 1; }
            100% { transform: translate(100px, 110vh) rotate(360deg); opacity: 0; }
        }
        
        @keyframes blow {
            0% { transform: translate(-10vw, 0) rotate(0deg); opacity: 0; top: 50%; }
            20% { opacity: 1; }
            100% { transform: translate(110vw, -100px) rotate(180deg); opacity: 0; top: 50%; }
        }

        /* --- SLIDING DROPLET EFFECT --- */
        .slide-drop {
            position: fixed;
            top: -50px;
            z-index: 15;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.5));
            border-radius: 50px;
            pointer-events: none;
            backdrop-filter: blur(2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .slide-drop.greenish {
            background: linear-gradient(to bottom, rgba(100, 255, 100, 0), rgba(150, 255, 150, 0.6));
            box-shadow: 0 0 10px rgba(100, 255, 100, 0.2);
        }
        @keyframes slideDown {
            0% { transform: translateY(0) scaleY(1); opacity: 0; }
            10% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translateY(120vh) scaleY(1.2); opacity: 0; }
        }

        /* --- UI ELEMENTS --- */
        #cursor {
            position: fixed; width: 20px; height: 20px;
            border: 1px solid white; border-radius: 50%;
            pointer-events: none; transform: translate(-50%, -50%);
            z-index: 10000; transition: width 0.2s, height 0.2s, background 0.2s;
            mix-blend-mode: difference;
        }
        #cursor.hovered { width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); }

        .card {
            width: 90%; 
            max-width: 380px; 
            padding: 30px 25px;
            background: var(--glass-bg);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px; text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            transition: opacity 1s ease;
        }
        
        .avatar {
            width: 100px; height: 100px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.2);
            margin-bottom: 15px; object-fit: cover;
        }

        .username { font-size: 1.8rem; font-weight: 700; margin-bottom: 5px; }
        
        .badges { margin-bottom: 15px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; }
        .badge {
            background: rgba(255,255,255,0.05); padding: 4px 8px;
            border-radius: 6px; font-size: 0.7rem; color: var(--accent);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .links { display: flex; justify-content: center; gap: 20px; margin-bottom: 25px; margin-top: 15px;}
        .links a { color: var(--accent); font-size: 1.3rem; transition: 0.3s; padding: 5px; }
        .links a:hover { color: #fff; transform: translateY(-3px); }

        .player-ui {
            background: rgba(0,0,0,0.3); border-radius: 12px; padding: 15px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .progress-container {
            width: 100%; height: 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px; margin: 10px 0; overflow: hidden;
            display: flex; align-items: center;
        }
        .progress-bar { height: 100%; width: 0%; background: var(--accent); transition: width 0.1s linear; }
        
        .controls { display: flex; align-items: center; justify-content: center; gap: 25px; margin-bottom: 10px;}
        .ctrl-btn { background: none; border: none; color: #ccc; font-size: 1.2rem; transition: 0.2s; padding: 5px; }
        .ctrl-btn:hover { color: #fff; transform: scale(1.2); }
        .play-btn { font-size: 1.6rem; color: #fff; }

        .volume-control {
            display: flex; align-items: center; justify-content: center;
            gap: 10px; margin-bottom: 10px; font-size: 0.8rem;
        }
        input[type=range] {
            -webkit-appearance: none; width: 80px; height: 3px; background: rgba(255,255,255,0.2);
            border-radius: 5px; outline: none;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none; appearance: none;
            width: 12px; height: 12px; border-radius: 50%; 
            background: var(--accent); cursor: pointer;
        }

        .lyrics-box {
            height: 50px; font-size: 0.7rem; color: rgba(255,255,255,0.6);
            font-style: italic; margin-top: 10px; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
            padding: 0 5px;
        }

        .footer {
            margin-top: 20px; font-size: 0.65rem; color: #555;
            display: flex; justify-content: space-between; padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        #overlay {
            position: fixed; inset: 0; background: #000; z-index: 9999;
            display: flex; align-items: center; justify-content: center; flex-direction: column;
            cursor: pointer; transition: opacity 0.8s;
        }
        .click-text { letter-spacing: 4px; animation: breathe 2s infinite; text-align: center; margin: 0 10px; }
        @keyframes breathe { 0%, 100% { opacity: 0.4; } 50% { opacity: 1; } }

        @media (max-width: 768px) {
            body { cursor: auto; }
            #cursor { display: none; }
            * { cursor: auto !important; }
            .card { width: 88%; padding: 25px 20px; }
            .links a { font-size: 1.5rem; }
            .ctrl-btn { font-size: 1.4rem; }
            .play-btn { font-size: 1.8rem; }
            .click-text { font-size: 0.9rem; letter-spacing: 2px; }
            input[type=range]::-webkit-slider-thumb { width: 15px; height: 15px; }
        }
    </style>
</head>
<body>

    <div id="cursor"></div>

    <div id="overlay">
        <div class="click-text">[ INITIALIZING... ]</div>
        <div style="font-size: 0.8rem; margin-top: 10px; opacity: 0.5;">Tap to Access System</div>
    </div>

    <img id="bg-image" class="bg-layer" src="" alt="">
    <video id="bg-video" class="bg-layer" loop muted playsinline></video>
    
    <div class="rain-window" id="rain-window"></div> 

    <div class="card" id="main-card" style="opacity:0">
        <div class="card-content">
            <img src="https://scontent.fmnl3-4.fna.fbcdn.net/v/t39.30808-6/475687044_1306906997102854_5197075266384357703_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=a5f93a&_nc_eui2=AeFZ7caDZEXQhVFX0RGVbvJSRYAWgSF3QiBFgBaBIXdCIOuAKBLpFTWkJp5Ie9ewoufhNdjNRPiidF633snSoay4&_nc_ohc=77RNBuqIJegQ7kNvwHwst1U&_nc_oc=AdkejvgpMoWfBk7zDMoKbegOkkpgaN-du_g3rCZpMRE4WZQt48QSc1hHevd9oJn05i4&_nc_zt=23&_nc_ht=scontent.fmnl3-4.fna&_nc_gid=3TIaa_tnnfb0dEM1jhJKjQ&oh=00_AfmQrZAj_ua5JtRSfxubSfmhgQ0mPBy5tFYm-TwmoI4oRA&oe=6942ABA8" class="avatar" alt="Avatar">
            <div class="username">Rensu</div>
            
            <div class="badges">
                <span class="badge">DEV</span>
                <span class="badge">PHP</span>
                <span class="badge">SYS</span>
            </div>

            <div style="margin: 15px 0; font-size: 0.8rem; color: #ccc; height: 20px;">
                <span style="color: var(--accent);">></span> <span id="typing"></span><span style="animation: blink 0.7s infinite">|</span>
            </div>

            <div class="links">
                <a href="https://github.com/rence141" target="_blank" class="hover-trigger"><i class="fab fa-github"></i></a>
                <a href="#" class="hover-trigger"><i class="fab fa-discord"></i></a>
                <a href="#" class="hover-trigger"><i class="fab fa-steam"></i></a>
            </div>

            <div class="player-ui">
                <div style="font-size: 0.8rem; font-weight: bold;" id="song-title">Select Track</div>
                
                <div class="progress-container hover-trigger" id="progress-container">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>

                <div class="controls">
                    <button class="ctrl-btn hover-trigger" id="prev-btn"><i class="fas fa-backward"></i></button>
                    <button class="ctrl-btn play-btn hover-trigger" id="play-btn"><i class="fas fa-play"></i></button>
                    <button class="ctrl-btn hover-trigger" id="next-btn"><i class="fas fa-forward"></i></button>
                </div>
                
                <div class="volume-control">
                    <i class="fas fa-volume-down"></i>
                    <input type="range" class="hover-trigger" id="vol-slider" min="0" max="1" step="0.05" value="1">
                </div>

                <div class="lyrics-box" id="lyrics-box">"Music is the language of the spirit."</div>
            </div>

            <div class="footer">
                <span id="clock">00:00</span>
                <span><i class="fa fa-eye"></i> <?= $views ?></span>
            </div>
        </div>
    </div>

    <audio id="audio-player"></audio>

    <script>
        // --- CONFIGURATION ---
        const playlist = [
            {
                audio: 'assets/mymusic.mp3', 
                title: 'Isabella\'s Lullaby',
                accent: '#e09f3e', 
                bgType: 'image',
                bgSrc: 'assets/bg1.png', 
                effect: 'fall', 
                lyrics: "Let me sing a lullaby...<br>As you close your eyes..."
            },
            {
                audio: 'assets/music2.mp3',
                title: 'Spirited Away - Rain',
                accent: '#90e0ef',
                bgType: 'video',
                bgSrc: 'assets/bg2.mp4', 
                effect: 'rain', 
                lyrics: "Inochi no namae...<br>Returning to that place."
            },
            {
                audio: 'assets/music3.mp3',
                title: 'Howl\'s Moving Castle',
                accent: '#9ef01a', 
                bgType: 'video',
                bgSrc: 'assets/bg4.mp4', 
                effect: 'blow', 
                lyrics: "The waltz of life...<br>Magic in the air."
            },
            {
                audio: 'assets/music4.mp3',
                title: 'Aegis of Bruises Genesus',
                accent: '#c6ba0cf7', 
                bgType: 'video',
                bgSrc: 'assets/bg5.mp4', 
                lyrics: "Adventure onwards...<br>Traveler regards."
            },
            {
                audio: 'assets/music5.mp3',
                title: 'Hollow Knight - Greenpath',
                accent: '#1c8d02', 
                bgType: 'video',
                bgSrc: 'assets/bg6.mp4', 
                effect: 'dripping', 
                lyrics: "Unbeknown Knight...<br>Favorous child."
            }
        ];

        let currentTrack = 0;
        let leafInterval;

        // --- DOM ---
        const audio = document.getElementById('audio-player');
        const bgImage = document.getElementById('bg-image');
        const bgVideo = document.getElementById('bg-video');
        const root = document.documentElement;
        
        // --- AUDIO HELPERS ---
        function fadeOutAudio(callback) {
            if (audio.paused) { callback(); return; }
            let fadeOut = setInterval(() => {
                // UPDATED: Faster fade step (0.1)
                if (audio.volume > 0.1) {
                    audio.volume -= 0.1;
                } else {
                    clearInterval(fadeOut);
                    audio.pause();
                    audio.volume = 1.0;
                    callback();
                }
            }, 40); // 40ms interval = approx 0.4s fade out
        }

        function fadeInAudio() {
            audio.volume = 0;
            audio.play().then(() => {
                let fadeIn = setInterval(() => {
                    const targetVol = parseFloat(document.getElementById('vol-slider').value);
                    // UPDATED: Faster fade in step (0.1)
                    if (audio.volume < targetVol - 0.1) {
                        audio.volume += 0.1;
                    } else {
                        clearInterval(fadeIn);
                    }
                }, 40);
            }).catch(e => console.log("Autoplay blocked"));
        }

        // --- EFFECTS ENGINE ---
        function clearEffects() {
            clearInterval(leafInterval);
            document.getElementById('rain-window').classList.remove('active'); 
            document.querySelectorAll('.leaf').forEach(e => e.remove());
            document.querySelectorAll('.slide-drop').forEach(e => e.remove());
        }

        function triggerEffect(type) {
            clearEffects();

            if (type === 'rain') {
                document.getElementById('rain-window').classList.add('active');
                createRainDroplets(); 
            } 
            else if (type === 'fall' || type === 'blow') {
                const colorClass = (type === 'fall') ? 'orange' : 'green';
                const animName = (type === 'fall') ? 'fall' : 'blow';
                
                leafInterval = setInterval(() => {
                    const leaf = document.createElement('div');
                    leaf.classList.add('leaf', colorClass);
                    if(type === 'fall') leaf.style.left = Math.random() * 100 + 'vw';
                    else leaf.style.top = Math.random() * 100 + 'vh';

                    const size = Math.random() * 20 + 15;
                    leaf.style.width = `${size}px`; leaf.style.height = `${size}px`;
                    
                    const duration = Math.random() * 5 + 5;
                    leaf.style.animation = `${animName} ${duration}s linear forwards`;
                    
                    document.body.appendChild(leaf);
                    setTimeout(() => leaf.remove(), duration * 1000);
                }, 800);
            }
            else if (type === 'dripping') {
                createSlidingDroplets();
            }
        }

        function createRainDroplets() {
            const container = document.getElementById('rain-window');
            container.innerHTML = '';
            const dropCount = window.innerWidth < 768 ? 40 : 80;
            for(let i=0; i<dropCount; i++) {
                const drop = document.createElement('div');
                drop.classList.add('droplet');
                drop.style.left = Math.random() * 100 + 'vw';
                drop.style.top = Math.random() * 100 + 'vh';
                const size = Math.random() * 10 + 2; 
                drop.style.width = size + 'px';
                drop.style.height = size + 'px';
                drop.style.opacity = Math.random() * 0.5 + 0.3;
                container.appendChild(drop);
            }
        }

        function createSlidingDroplets() {
            leafInterval = setInterval(() => {
                const drop = document.createElement('div');
                drop.classList.add('slide-drop', 'greenish');
                drop.style.left = Math.random() * 100 + 'vw';
                const width = Math.random() * 2 + 1; 
                const height = Math.random() * 30 + 10;
                drop.style.width = width + 'px';
                drop.style.height = height + 'px';
                const duration = Math.random() * 2 + 1.5;
                drop.style.animation = `slideDown ${duration}s ease-in forwards`;
                document.body.appendChild(drop);
                setTimeout(() => drop.remove(), duration * 1000);
            }, 150);
        }

        // --- PLAYER LOGIC ---
        function loadTrack(index, isTransition = false) {
            const track = playlist[index];
            
            // 1. FADE OUT VISUALS
            bgImage.classList.remove('active');
            bgVideo.classList.remove('active');
            
            // UPDATED: 500ms delay matches the faster CSS transition
            const delay = isTransition ? 500 : 0; 

            setTimeout(() => {
                root.style.setProperty('--accent', track.accent);
                document.getElementById('song-title').innerText = track.title;
                document.getElementById('lyrics-box').innerHTML = track.lyrics;

                if (track.bgType === 'video') {
                    bgVideo.src = track.bgSrc;
                    bgVideo.onloadeddata = () => {
                        bgVideo.play();
                        bgVideo.classList.add('active'); 
                    };
                } else {
                    bgVideo.pause();
                    bgImage.src = track.bgSrc;
                    bgImage.onload = () => {
                        bgImage.classList.add('active'); 
                    };
                }

                audio.src = track.audio;
                triggerEffect(track.effect);
                
                if (isTransition) {
                    fadeInAudio();
                    document.getElementById('play-btn').innerHTML = '<i class="fas fa-pause"></i>';
                } else {
                    document.getElementById('play-btn').innerHTML = '<i class="fas fa-play"></i>';
                }

            }, delay);
        }

        // --- CONTROLS ---
        document.getElementById('play-btn').addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
                document.getElementById('play-btn').innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                audio.pause();
                document.getElementById('play-btn').innerHTML = '<i class="fas fa-play"></i>';
            }
        });

        document.getElementById('next-btn').addEventListener('click', () => {
            fadeOutAudio(() => {
                currentTrack = (currentTrack + 1) % playlist.length;
                loadTrack(currentTrack, true);
            });
        });

        document.getElementById('prev-btn').addEventListener('click', () => {
            fadeOutAudio(() => {
                currentTrack = (currentTrack - 1 + playlist.length) % playlist.length;
                loadTrack(currentTrack, true);
            });
        });

        document.getElementById('vol-slider').addEventListener('input', (e) => {
            audio.volume = e.target.value;
        });

        audio.addEventListener('timeupdate', (e) => {
            const { duration, currentTime } = e.target;
            if(duration) {
                const progressPercent = (currentTime / duration) * 100;
                document.getElementById('progress-bar').style.width = `${progressPercent}%`;
            }
        });
        
        audio.addEventListener('ended', () => {
            fadeOutAudio(() => {
                currentTrack = (currentTrack + 1) % playlist.length;
                loadTrack(currentTrack, true);
            });
        });

        document.getElementById('progress-container').addEventListener('click', function(e) {
            const width = this.clientWidth;
            const clickX = e.offsetX;
            const duration = audio.duration;
            if(duration) {
                audio.currentTime = (clickX / width) * duration;
            }
        });

        // --- INIT ---
        document.getElementById('overlay').addEventListener('click', () => {
            document.getElementById('overlay').style.opacity = '0';
            setTimeout(() => document.getElementById('overlay').style.display = 'none', 800);
            document.getElementById('main-card').style.opacity = '1';
            loadTrack(0, false); 
            startTypewriter();
        });

        if (window.matchMedia("(hover: hover)").matches) {
            document.addEventListener('mousemove', (e) => {
                const cursor = document.getElementById('cursor');
                cursor.style.top = e.clientY + 'px';
                cursor.style.left = e.clientX + 'px';
            });
            document.querySelectorAll('.hover-trigger').forEach(el => {
                el.addEventListener('mouseenter', () => document.getElementById('cursor').classList.add('hovered'));
                el.addEventListener('mouseleave', () => document.getElementById('cursor').classList.remove('hovered'));
            });
        }

        function startTypewriter() {
            const texts = ["Stay with me for a while", "Let's make memories together", "I'm here for you", "Take my hand", "Please Don't give up", "you can endure it", "I can help you", "Please believe in me", "please....", "I'm....", "sorry..."];
            let count = 0; 
            let index = 0; 
            let currentText = ""; 
            let isDeleting = false;

            (function type() {
                if (count === texts.length) count = 0;
                currentText = texts[count];
                if (isDeleting) index--; else index++;
                document.getElementById('typing').textContent = currentText.slice(0, index);
                let typeSpeed = 100;
                if (isDeleting) typeSpeed = 50;
                if (!isDeleting && index === currentText.length) {
                    typeSpeed = 2000; isDeleting = true;
                } else if (isDeleting && index === 0) {
                    isDeleting = false; count++; typeSpeed = 500;
                }
                setTimeout(type, typeSpeed);
            }());
        }

        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }, 1000);
    </script>
</body>
</html>