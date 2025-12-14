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
                bgSrc: 'assets/Bg5.mp4', 
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
            },
            {
                audio: 'assets/music6.mp3',
                title: 'OMORI - My Time',
                accent: '#ffb7c5',
                bgType: 'video',
                bgSrc: 'assets/bg7.mp4',
                effect: 'slow-glitch',
                lyrics: "Close your eyes...<br>You’ll be here soon.",
                // CLIMAX TIME SET TO 44 SECONDS
                climaxTime: 44
            }
        ];

        // --- TYPEWRITER CONFIGURATION ---
        const originalTexts = [
            "Stay with me for a while", 
            "Let's make memories together", 
            "I'm here for you", 
            "Take my hand", 
            "Please Don't give up", 
            "you can endure it", 
            "I can help you", 
            "Please believe in me", 
            "please....", 
            "I'm....", 
            "sorry..."
        ];

        const omoriTexts = [
            "SLEEP...",
            "DEPRESSED...",
            "Everything is going to be okay?",
            "Waiting for something to happen?",
            "Oyasumi",
            "DIE DIE DIE"
        ];

        let currentTexts = originalTexts; 

        // Typewriter State Variables
        let typeCount = 0; 
        let typeIndex = 0; 
        let currentText = ""; 
        let isDeleting = false;

        let currentTrack = 0;
        let leafInterval;

        // --- DOM ---
        const audio = document.getElementById('audio-player');
        const bgImage = document.getElementById('bg-image');
        const bgVideo = document.getElementById('bg-video');
        const pfpAlt = document.getElementById('pfp-alt'); 
        const root = document.documentElement;
        
        // --- AUDIO HELPERS ---
        function fadeOutAudio(callback) {
            if (audio.paused) { callback(); return; }
            let fadeOut = setInterval(() => {
                if (audio.volume > 0.1) {
                    audio.volume -= 0.1;
                } else {
                    clearInterval(fadeOut);
                    audio.pause();
                    bgVideo.pause(); // Sync Video Pause
                    audio.volume = 1.0;
                    callback();
                }
            }, 40);
        }

        function fadeInAudio() {
            audio.volume = 0;
            audio.play().then(() => {
                // Sync Video Play
                if(playlist[currentTrack].bgType === 'video') bgVideo.play();
                
                let fadeIn = setInterval(() => {
                    const targetVol = parseFloat(document.getElementById('vol-slider').value);
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
            
            const delay = isTransition ? 500 : 0; 

            setTimeout(() => {
                root.style.setProperty('--accent', track.accent);
                document.getElementById('song-title').innerText = track.title;
                document.getElementById('lyrics-box').innerHTML = track.lyrics;

                // --- 1. RESET PFP ---
                pfpAlt.style.opacity = 0; 
                if (track.title.includes('OMORI')) {
                    pfpAlt.src = "assets/omori-emotions.gif";
                } else {
                    pfpAlt.src = ""; 
                }

                // --- 2. SWITCH TYPEWRITER TEXT ---
                currentTexts = originalTexts;
                typeCount = 0;
                typeIndex = 0;
                isDeleting = false;
                document.getElementById('typing').textContent = "";

                // --- 3. BACKGROUND ---
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
        
        // UPDATED: Sync Video Play/Pause
        document.getElementById('play-btn').addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
                if(playlist[currentTrack].bgType === 'video') bgVideo.play();
                document.getElementById('play-btn').innerHTML = '<i class="fas fa-pause"></i>';
            } else {
                audio.pause();
                bgVideo.pause();
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

        // --- TIME UPDATE & CLIMAX CHECKER ---
        audio.addEventListener('timeupdate', (e) => {
            const { duration, currentTime } = e.target;
            const track = playlist[currentTrack];

            if(duration) {
                const progressPercent = (currentTime / duration) * 100;
                document.getElementById('progress-bar').style.width = `${progressPercent}%`;
            }

            // CHECK FOR CLIMAX (Text & PFP Switcher)
            if (track.title.includes('OMORI') && track.climaxTime) {
                if (currentTime >= track.climaxTime) {
                    // CLIMAX REACHED
                    pfpAlt.style.opacity = 1; 
                    if(currentTexts !== omoriTexts) {
                        currentTexts = omoriTexts;
                        typeCount = 0;
                        typeIndex = 0;
                        isDeleting = false;
                        document.getElementById('typing').textContent = "";
                    }
                } else {
                    // BEFORE CLIMAX
                    pfpAlt.style.opacity = 0; 
                    if(currentTexts !== originalTexts) {
                        currentTexts = originalTexts;
                        typeCount = 0;
                        typeIndex = 0;
                        isDeleting = false;
                        document.getElementById('typing').textContent = "";
                    }
                }
            }
        });
        
        audio.addEventListener('ended', () => {
            fadeOutAudio(() => {
                currentTrack = (currentTrack + 1) % playlist.length;
                loadTrack(currentTrack, true);
            });
        });

        // --- UPDATED: CLICK TO SEEK (SYNCHRONOUS VIDEO) ---
        document.getElementById('progress-container').addEventListener('click', function(e) {
            const width = this.clientWidth;
            const clickX = e.offsetX;
            const duration = audio.duration;
            
            if(duration) {
                const newTime = (clickX / width) * duration;
                
                // 1. Set Audio Time
                audio.currentTime = newTime;
                
                // 2. Set Video Time (Synchronize)
                const track = playlist[currentTrack];
                if (track.bgType === 'video') {
                    bgVideo.currentTime = newTime;
                }
            }
        });

        // --- INIT ---
        document.getElementById('overlay').addEventListener('click', () => {
            document.getElementById('overlay').style.opacity = '0';
            setTimeout(() => document.getElementById('overlay').style.display = 'none', 800);
            document.getElementById('main-card').style.opacity = '1';
            loadTrack(0, false); 
            startTypewriter(); // Start the loop
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

        // --- TYPEWRITER LOGIC ---
        function startTypewriter() {
            (function type() {
                if (typeCount >= currentTexts.length) typeCount = 0;
                currentText = currentTexts[typeCount];
                
                if (isDeleting) typeIndex--; else typeIndex++;
                
                document.getElementById('typing').textContent = currentText.slice(0, typeIndex);
                
                let typeSpeed = 100;
                if (isDeleting) typeSpeed = 50;
                
                if (!isDeleting && typeIndex === currentText.length) {
                    typeSpeed = 2000; 
                    isDeleting = true;
                } else if (isDeleting && typeIndex === 0) {
                    isDeleting = false; 
                    typeCount++; 
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