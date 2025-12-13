<?php
// --- BACKEND: VIEW COUNTER (ROBUST) ---
$stats_file = 'views.json';

// 1. Initialize default structure
$default_data = ['views' => 0, 'ips' => []];

// 2. Create file if it doesn't exist
if (!file_exists($stats_file)) {
    file_put_contents($stats_file, json_encode($default_data));
}

// 3. Read file safely
$json_content = file_get_contents($stats_file);
$data = json_decode($json_content, true);

// 4. Data Integrity Check: If JSON is corrupt or empty, reset it to prevent errors
if (!is_array($data) || !isset($data['views']) || !isset($data['ips'])) {
    $data = $default_data;
}

$user_ip = $_SERVER['REMOTE_ADDR'];
$ip_hash = md5($user_ip);

// 5. UPDATE LOGIC
// Check if IP is new. 
// TIP: To test if it works, comment out the "if" condition temporarily so it counts every refresh.
if (!in_array($ip_hash, $data['ips'])) {
    $data['views']++;
    $data['ips'][] = $ip_hash;
    
    // 6. Write with LOCK_EX (Exclusive Lock) to prevent file corruption 
    // when multiple users visit at the same time.
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
            /* Hide cursor only on non-touch devices */
            cursor: none; 
        }

        /* --- BACKGROUND LAYER --- */
        .bg-layer {
            position: fixed; inset: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -2; 
            opacity: 0; transition: opacity 1.5s ease;
            filter: brightness(0.6) contrast(1.1);
        }
        .bg-layer.active { opacity: 1; }

        /* --- RAIN DROPLETS (WINDOW EFFECT) --- */
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
            /* MOBILE FIX: Use percentage width with a max-limit */
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
            width: 100%; height: 10px; /* Thicker for mobile touch */
            background: rgba(255,255,255,0.1);
            border-radius: 5px; margin: 10px 0; overflow: hidden;
            display: flex; align-items: center;
        }
        .progress-bar { height: 100%; width: 0%; background: var(--accent); transition: width 0.1s linear; }
        
        .controls { display: flex; align-items: center; justify-content: center; gap: 25px; margin-bottom: 10px;}
        .ctrl-btn { background: none; border: none; color: #ccc; font-size: 1.2rem; transition: 0.2s; padding: 5px; }
        .ctrl-btn:hover { color: #fff; transform: scale(1.2); }
        .play-btn { font-size: 1.6rem; color: #fff; }

        /* --- VOLUME CONTROL --- */
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

        /* --- MOBILE MEDIA QUERY --- */
        @media (max-width: 768px) {
            body { cursor: auto; } /* Restore default cursor */
            #cursor { display: none; } /* Hide custom cursor */
            * { cursor: auto !important; }
            
            .card { width: 88%; padding: 25px 20px; }
            .links a { font-size: 1.5rem; }
            .ctrl-btn { font-size: 1.4rem; }
            .play-btn { font-size: 1.8rem; }
            .click-text { font-size: 0.9rem; letter-spacing: 2px; }
            
            /* Make volume slider thumb bigger for touch */
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
            }
        ];

        let currentTrack = 0;
        let leafInterval;

        // --- DOM ---
        const audio = document.getElementById('audio-player');
        const bgImage = document.getElementById('bg-image');
        const bgVideo = document.getElementById('bg-video');
        const root = document.documentElement;
        
        // --- EFFECTS ENGINE ---
        function clearEffects() {
            clearInterval(leafInterval);
            document.getElementById('rain-window').classList.remove('active'); 
            document.querySelectorAll('.leaf').forEach(e => e.remove());
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
        }

        function createRainDroplets() {
            const container = document.getElementById('rain-window');
            container.innerHTML = '';
            // Reduced droplet count for mobile performance
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

        // --- PLAYER LOGIC ---
        function loadTrack(index) {
            const track = playlist[index];
            root.style.setProperty('--accent', track.accent);
            document.getElementById('song-title').innerText = track.title;
            document.getElementById('lyrics-box').innerHTML = track.lyrics;
            
            if (track.bgType === 'video') {
                bgImage.classList.remove('active');
                bgVideo.src = track.bgSrc;
                bgVideo.play().catch(e => console.log('Video Play Error', e));
                bgVideo.classList.add('active');
            } else {
                bgVideo.classList.remove('active');
                bgVideo.pause();
                bgImage.src = track.bgSrc;
                bgImage.classList.add('active');
            }

            audio.src = track.audio;
            triggerEffect(track.effect);
            
            // Only attempt play if user has interacted (will happen via overlay click)
            audio.play().then(() => {
                document.getElementById('play-btn').innerHTML = '<i class="fas fa-pause"></i>';
            }).catch(e => {
                console.log("Auto-play blocked, waiting for interaction");
                document.getElementById('play-btn').innerHTML = '<i class="fas fa-play"></i>';
            });
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
            currentTrack = (currentTrack + 1) % playlist.length;
            loadTrack(currentTrack);
        });

        document.getElementById('prev-btn').addEventListener('click', () => {
            currentTrack = (currentTrack - 1 + playlist.length) % playlist.length;
            loadTrack(currentTrack);
        });

        document.getElementById('vol-slider').addEventListener('input', (e) => {
            audio.volume = e.target.value;
        });

        audio.addEventListener('timeupdate', (e) => {
            const { duration, currentTime } = e.target; // Changed srcElement to target for mobile
            const progressPercent = (currentTime / duration) * 100;
            document.getElementById('progress-bar').style.width = `${progressPercent}%`;
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
            loadTrack(0);
            startTypewriter();
        });

        // Mouse Cursor - Only add listener if not mobile (Basic check)
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

        // --- UPDATED TYPEWRITER ---
        function startTypewriter() {
            const texts = ["Stay with me for a while", "Let's make memories together", "I'm here for you", "Take my hand", "Please Don't give up", "you can endure it", "I can help you", "Please believe in me", "please....", "I'm....", "sorry..."];
            let count = 0; 
            let index = 0; 
            let currentText = ""; 
            let isDeleting = false;

            (function type() {
                if (count === texts.length) count = 0;
                currentText = texts[count];

                if (isDeleting) {
                    index--;
                } else {
                    index++;
                }

                document.getElementById('typing').textContent = currentText.slice(0, index);

                let typeSpeed = 100;
                if (isDeleting) typeSpeed = 50;

                if (!isDeleting && index === currentText.length) {
                    typeSpeed = 2000;
                    isDeleting = true;
                } else if (isDeleting && index === 0) {
                    isDeleting = false;
                    count++;
                    typeSpeed = 500;
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