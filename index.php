<?php
// --- BACKEND: SIMPLE VIEW COUNTER ---
$stats_file = 'views.json';
$views = 0;

// Initialize file if missing
if (!file_exists($stats_file)) {
    file_put_contents($stats_file, json_encode(['views' => 0, 'ips' => []]));
}

// Read Data
$data = json_decode(file_get_contents($stats_file), true);
$user_ip = $_SERVER['REMOTE_ADDR'];
$ip_hash = md5($user_ip);

// Update count if new IP
if (!in_array($ip_hash, $data['ips'])) {
    $data['views']++;
    $data['ips'][] = $ip_hash;
    file_put_contents($stats_file, json_encode($data));
}
$views = $data['views'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@Rence141 // Portfolio</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* --- VARIABLES & RESET --- */
        :root {
            --primary: #ffffff;
            --accent: #a8b2d1; 
            --glass-bg: rgba(20, 20, 20, 0.65);
            --glass-border: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; cursor: none; }
        
        body {
            background-color: #050505;
            color: var(--primary);
            font-family: 'JetBrains Mono', monospace;
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            perspective: 1000px;
            transition: color 1s ease;
        }

        /* --- CUSTOM CURSOR --- */
        #cursor {
            position: fixed; top: 0; left: 0;
            width: 20px; height: 20px;
            border: 1px solid white; border-radius: 50%;
            pointer-events: none; transform: translate(-50%, -50%);
            z-index: 10000;
            transition: width 0.2s, height 0.2s, background 0.2s;
            mix-blend-mode: difference;
        }
        #cursor.hovered {
            width: 40px; height: 40px;
            background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(2px);
        }

        /* --- BACKGROUND ASSETS --- */
        .bg-asset {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; z-index: -2; 
            opacity: 0; /* Hidden by default */
            filter: contrast(1.1) brightness(0.8);
            transition: opacity 1.5s ease; 
        }
        .bg-asset.active { opacity: 0.5; }

        .noise-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://cdnjs.cloudflare.com/ajax/libs/topcoat-icons/0.2.0/svg/noise.svg');
            opacity: 0.05; z-index: -1; pointer-events: none;
        }

        /* --- OVERLAY --- */
        #overlay {
            position: fixed; inset: 0; background: #000; z-index: 9999;
            display: flex; align-items: center; justify-content: center; flex-direction: column;
            cursor: pointer; transition: opacity 0.8s ease, visibility 0.8s;
        }
        #overlay .click-text {
            font-size: 1.2rem; letter-spacing: 4px;
            animation: breathe 2s infinite ease-in-out; opacity: 0.8;
        }

        /* --- CARD --- */
        .card {
            width: 380px; padding: 30px 25px;
            background: var(--glass-bg);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 20px; text-align: center;
            box-shadow: var(--card-shadow);
            opacity: 0; transform: translateY(20px) scale(0.9);
            transition: transform 0.1s ease-out, opacity 1s ease;
            z-index: 10; position: relative; transform-style: preserve-3d;
            max-height: 90vh; overflow-y: auto; 
        }
        .card::-webkit-scrollbar { display: none; }
        .card-content { transform: translateZ(40px); }
        
        .avatar {
            width: 100px; height: 100px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.2);
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
            margin-bottom: 15px; object-fit: cover; transition: transform 0.3s;
        }
        .avatar:hover { transform: scale(1.05) rotate(5deg); border-color: #fff; }

        .username {
            font-size: 1.8rem; font-weight: 700; margin-bottom: 5px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        .badges { margin-bottom: 15px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;}
        .badge {
            background: rgba(255,255,255,0.05); padding: 4px 8px;
            border-radius: 6px; font-size: 0.7rem;
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--accent); transition: background 0.3s, color 1s;
        }
        .badge:hover { background: rgba(255,255,255,0.15); color: #fff; }

        .bio-box { height: 25px; margin-bottom: 20px; color: #ccc; font-size: 0.8rem; }

        .links { display: flex; justify-content: center; gap: 20px; margin-bottom: 25px; }
        .links a {
            color: var(--accent); font-size: 1.3rem;
            transition: all 0.3s; text-decoration: none;
        }
        .links a:hover { color: #fff; transform: translateY(-3px) scale(1.1); }

        /* --- PLAYER UI --- */
        .player-ui {
            background: rgba(0,0,0,0.3); border-radius: 12px;
            padding: 15px; 
            margin-top: 10px; border: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; gap: 8px;
        }
        
        .song-title { font-size: 0.85rem; font-weight: bold; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* TIME BAR CSS */
        .time-display {
            display: flex; justify-content: space-between;
            font-size: 0.6rem; color: #aaa; margin-bottom: 2px;
        }
        .progress-container {
            width: 100%; height: 4px;
            background: rgba(255,255,255,0.2);
            border-radius: 2px; cursor: none;
            overflow: hidden;
            margin-bottom: 10px;
        }
        .progress-bar {
            height: 100%; width: 0%;
            background: var(--accent);
            border-radius: 2px;
            transition: width 0.1s linear, background 1s;
        }
        .progress-container:hover .progress-bar {
            filter: brightness(1.2);
        }

        /* Controls Row */
        .controls { display: flex; align-items: center; justify-content: center; gap: 20px; }
        .ctrl-btn {
            background: none; border: none; color: #ccc; font-size: 1rem;
            cursor: none; transition: color 0.2s, transform 0.2s;
        }
        .ctrl-btn:hover { color: #fff; transform: scale(1.2); }
        .play-btn { font-size: 1.2rem; color: #fff; }

        /* Volume Slider */
        .volume-container { display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 0.7rem; color: #aaa; width: 100%;}
        input[type=range] {
            -webkit-appearance: none; width: 60%; height: 4px; background: rgba(255,255,255,0.2); border-radius: 2px; outline: none;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none; width: 10px; height: 10px; background: var(--accent); border-radius: 50%; cursor: none;
            transition: background 1s;
        }

        /* Lyrics Box */
        .lyrics-box {
            height: 60px; overflow-y: auto; 
            font-size: 0.7rem; color: rgba(255,255,255,0.7);
            font-style: italic; line-height: 1.4;
            margin-top: 5px; padding: 5px;
            border-top: 1px solid rgba(255,255,255,0.05);
            text-align: center;
        }
        .lyrics-box::-webkit-scrollbar { width: 3px; }
        .lyrics-box::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 3px; }

        .footer {
            margin-top: 20px; font-size: 0.65rem; color: #555;
            display: flex; justify-content: space-between;
            padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.05);
        }

        /* --- ANIMATIONS --- */
        @keyframes breathe { 0%, 100% { opacity: 0.4; } 50% { opacity: 1; } }
        .fade-in-active { opacity: 1 !important; transform: translateY(0) scale(1) !important; }

        /* --- LEAVES --- */
        .leaf {
            position: fixed; top: -10vh; z-index: 1; pointer-events: none;
            background-image: url('https://media2.giphy.com/media/PhHRGsy8SuXcdDErKK/source.gif');
            background-size: contain; background-repeat: no-repeat; opacity: 0.8;
        }
        @keyframes sway {
            0% { transform: translateX(0) rotate(0deg); opacity: 0; }
            20% { opacity: 1; }
            100% { transform: translateX(150px) rotate(180deg); opacity: 0; top: 110vh; }
        }
    </style>
</head>
<body>

    <div id="cursor"></div>

    <div id="overlay">
        <div class="click-text">[ INITIALIZING... ]</div>
        <div style="font-size: 0.8rem; margin-top: 10px; opacity: 0.5;">Click anywhere to enter</div>
    </div>

    <video class="bg-asset" id="bg-video" loop muted playsinline>
        <source src="" type="video/mp4">
    </video>
    <img class="bg-asset" id="bg-image" src="" alt="Background">
    
    <div class="noise-overlay"></div>

    <div class="card" id="main-card">
        <div class="card-content">
            <img src="https://scontent.fmnl3-4.fna.fbcdn.net/v/t39.30808-6/475687044_1306906997102854_5197075266384357703_n.jpg?_nc_cat=102&ccb=1-7&_nc_sid=a5f93a&_nc_eui2=AeFZ7caDZEXQhVFX0RGVbvJSRYAWgSF3QiBFgBaBIXdCIOuAKBLpFTWkJp5Ie9ewoufhNdjNRPiidF633snSoay4&_nc_ohc=77RNBuqIJegQ7kNvwHwst1U&_nc_oc=AdkejvgpMoWfBk7zDMoKbegOkkpgaN-du_g3rCZpMRE4WZQt48QSc1hHevd9oJn05i4&_nc_zt=23&_nc_ht=scontent.fmnl3-4.fna&_nc_gid=3TIaa_tnnfb0dEM1jhJKjQ&oh=00_AfmQrZAj_ua5JtRSfxubSfmhgQ0mPBy5tFYm-TwmoI4oRA&oe=6942ABA8" class="avatar" alt="Avatar">
            
            <div class="username">Rensu</div>
            
            <div class="badges">
                <span class="badge">DEV</span>
                <span class="badge">PHP</span>
                <span class="badge">PH</span>
                <span class="badge">SYS</span>
            </div>

            <div class="bio-box">
                <span style="color: var(--accent); margin-right:5px; transition:color 1s;">></span>
                <span id="typing"></span><span class="cursor-blink">_</span>
            </div>

            <div class="links">
                <a href="https://github.com/rence141" target="_blank" class="hover-trigger"><i class="fab fa-github"></i></a>
                <a href="#" target="_blank" class="hover-trigger"><i class="fab fa-discord"></i></a>
                <a href="#" target="_blank" class="hover-trigger"><i class="fab fa-steam"></i></a>
                <a href="#" target="_blank" class="hover-trigger"><i class="fab fa-spotify"></i></a>
            </div>

            <div class="player-ui">
                <div class="song-title" id="song-title">Loading...</div>
                
                <div class="time-display">
                    <span id="curr-time">0:00</span>
                    <span id="dur-time">0:00</span>
                </div>
                <div class="progress-container hover-trigger" id="progress-container">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>

                <div class="controls">
                    <button class="ctrl-btn hover-trigger" id="prev-btn"><i class="fas fa-backward"></i></button>
                    <button class="ctrl-btn play-btn hover-trigger" id="play-btn"><i class="fas fa-play"></i></button>
                    <button class="ctrl-btn hover-trigger" id="next-btn"><i class="fas fa-forward"></i></button>
                </div>

                <div class="volume-container">
                    <i class="fas fa-volume-down"></i>
                    <input type="range" id="vol-slider" min="0" max="1" step="0.01" value="0.4" class="hover-trigger">
                    <i class="fas fa-volume-up"></i>
                </div>

                <div class="lyrics-box" id="lyrics-box">
                    </div>
            </div>

            <div class="footer">
                <span id="clock">00:00 AM</span>
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
                title: 'The Promised Neverland',
                bgType: 'image', 
                bgSrc: 'assets/bg1.png', 
                accent: '#a8b2d1',
                glassBg: 'rgba(20, 20, 30, 0.65)',
                lyrics: "Let me sing a lullaby...<br>As you close your eyes...<br>And drift away to sleep...<br>(Isabella's Lullaby)"
            },
            {
                audio: 'assets/music2.mp3',
                title: 'The Name of Life - Spirited Away',
                bgType: 'video',
                bgSrc: 'assets/bg2.mp4',
                accent: '#ffafcc',
                glassBg: 'rgba(40, 10, 20, 0.65)',
                lyrics: "Inochi no namae...<br>One Summer's Day...<br>Where the wind blows...<br>Returning to that place."
            },
            {
                audio: 'assets/music3.mp3',
                title: 'Howl\'s Moving Castle',
                bgType: 'video',
                bgSrc: 'assets/bg4.mp4',
                accent: '#5de0e6',
                glassBg: 'rgba(0, 20, 25, 0.65)',
                lyrics: "(Instrumental)<br>The waltz of life...<br>Spinning round and round...<br>Magic in the air."
            }
        ];

        let currentTrack = 0;

        // --- DOM ELEMENTS ---
        const overlay = document.getElementById('overlay');
        const card = document.getElementById('main-card');
        const audio = document.getElementById('audio-player');
        
        const bgVideo = document.getElementById('bg-video');
        const bgImage = document.getElementById('bg-image');
        
        const playBtn = document.getElementById('play-btn');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const volSlider = document.getElementById('vol-slider');
        
        const songTitle = document.getElementById('song-title');
        const lyricsBox = document.getElementById('lyrics-box');
        
        // Progress Elements
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const currTimeText = document.getElementById('curr-time');
        const durTimeText = document.getElementById('dur-time');

        const cursor = document.getElementById('cursor');
        const root = document.documentElement;

        // --- LOAD TRACK ---
        function loadTrack(index) {
            const track = playlist[index];
            
            bgVideo.classList.remove('active');
            bgImage.classList.remove('active');

            setTimeout(() => {
                audio.src = track.audio;
                audio.volume = volSlider.value;
                songTitle.innerText = track.title;
                lyricsBox.innerHTML = track.lyrics;
                root.style.setProperty('--accent', track.accent);
                root.style.setProperty('--glass-bg', track.glassBg);

                if (track.bgType === 'video') {
                    bgVideo.src = track.bgSrc;
                    bgVideo.play().catch(e => console.log('Video error', e));
                    setTimeout(() => bgVideo.classList.add('active'), 100);
                } else {
                    bgImage.src = track.bgSrc;
                    bgImage.onload = () => bgImage.classList.add('active');
                }

                playAudio();
            }, 800);
        }

        // --- CONTROLS ---
        function playAudio() {
            audio.play()
                .then(() => { playBtn.innerHTML = '<i class="fas fa-pause"></i>'; })
                .catch(e => console.log("Autoplay prevented:", e));
        }

        function togglePlay() {
            if (audio.paused) {
                playAudio();
            } else {
                audio.pause();
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        }

        function nextTrack() {
            currentTrack = (currentTrack + 1) % playlist.length;
            loadTrack(currentTrack);
        }

        function prevTrack() {
            currentTrack = (currentTrack - 1 + playlist.length) % playlist.length;
            loadTrack(currentTrack);
        }

        // --- PROGRESS BAR LOGIC ---
        function formatTime(seconds) {
            const min = Math.floor(seconds / 60);
            const sec = Math.floor(seconds % 60);
            return `${min}:${sec < 10 ? '0' : ''}${sec}`;
        }

        function updateProgress(e) {
            const { duration, currentTime } = e.srcElement;
            if (isNaN(duration)) return;
            
            const progressPercent = (currentTime / duration) * 100;
            progressBar.style.width = `${progressPercent}%`;
            
            currTimeText.innerText = formatTime(currentTime);
            durTimeText.innerText = formatTime(duration);
        }

        function setProgress(e) {
            const width = this.clientWidth;
            const clickX = e.offsetX;
            const duration = audio.duration;
            audio.currentTime = (clickX / width) * duration;
        }

        // --- LISTENERS ---
        playBtn.addEventListener('click', togglePlay);
        nextBtn.addEventListener('click', nextTrack);
        prevBtn.addEventListener('click', prevTrack);
        volSlider.addEventListener('input', (e) => { audio.volume = e.target.value; });
        
        audio.addEventListener('timeupdate', updateProgress);
        audio.addEventListener('ended', nextTrack);
        progressContainer.addEventListener('click', setProgress);

        // --- ENTER SEQUENCE ---
        let isEntered = false;
        overlay.addEventListener('click', () => {
            isEntered = true;
            overlay.style.opacity = '0';
            setTimeout(() => { overlay.style.display = 'none'; }, 800);
            
            card.classList.add('fade-in-active');
            loadTrack(currentTrack);
            typeWriter(); 
            createLeaves();
            startClock();
        });

        // --- VISUALS ---
        document.addEventListener('mousemove', (e) => {
            cursor.style.top = e.clientY + 'px';
            cursor.style.left = e.clientX + 'px';
            if(isEntered) {
                const xAxis = (window.innerWidth / 2 - e.clientX) / 20;
                const yAxis = (window.innerHeight / 2 - e.clientY) / 20;
                card.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
            }
        });
        document.querySelectorAll('.hover-trigger').forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hovered'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hovered'));
        });

        // --- TYPEWRITER ---
        const texts = ["Are you OK?", "The Horizon is Beautiful.", "Let's stay this way."];
        let textIndex = 0; let charIndex = 0; let isDeleting = false;
        const typingElement = document.getElementById('typing');

        function typeWriter() {
            const currentText = texts[textIndex];
            if (isDeleting) {
                typingElement.textContent = currentText.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typingElement.textContent = currentText.substring(0, charIndex + 1);
                charIndex++;
            }
            if (!isDeleting && charIndex === currentText.length) {
                isDeleting = true; setTimeout(typeWriter, 2000);
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false; textIndex = (textIndex + 1) % texts.length; setTimeout(typeWriter, 500);
            } else {
                setTimeout(typeWriter, isDeleting ? 50 : 100);
            }
        }

        // --- LEAVES ---
        function createLeaves() {
            setInterval(() => {
                const leaf = document.createElement('div');
                leaf.classList.add('leaf');
                leaf.style.left = Math.random() * 100 + 'vw';
                const size = Math.random() * 30 + 20;
                leaf.style.width = `${size}px`; leaf.style.height = `${size}px`;
                const duration = Math.random() * 5 + 6;
                leaf.style.animation = `sway ${duration}s linear forwards`;
                document.body.appendChild(leaf);
                setTimeout(() => leaf.remove(), duration * 1000);
            }, 1000);
        }

        // --- CLOCK ---
        function startClock() {
            const updateClock = () => {
                const now = new Date();
                document.getElementById('clock').innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            };
            setInterval(updateClock, 1000);
            updateClock();
        }
    </script>
</body>
</html>