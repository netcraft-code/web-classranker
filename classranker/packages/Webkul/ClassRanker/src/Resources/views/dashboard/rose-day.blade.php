<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Happy Rose Day My Love 🌹</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Crimson+Text:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --rose-red: #c41e3a;
            --rose-pink: #ff6b9d;
            --cream: #fff5f5;
            --dark-green: #2d4a2b;
            --gold: #d4af37;
        }

        body {
            font-family: 'Crimson Text', serif;
            background: linear-gradient(135deg, #1a0e0e 0%, #3d1a1a 50%, #1a0e0e 100%);
            color: var(--cream);
            overflow-x: hidden;
            min-height: 100vh;
            position: relative;
        }

        /* Floating petals animation */
        .petals {
            position: fixed;
            top: -10%;
            left: 0;
            width: 100%;
            height: 120%;
            pointer-events: none;
            z-index: 1;
        }

        .petal {
            position: absolute;
            width: 10px;
            height: 10px;
            background: radial-gradient(ellipse at center, var(--rose-pink) 0%, var(--rose-red) 100%);
            border-radius: 50% 0 50% 0;
            opacity: 0;
            animation: fall linear infinite;
        }

        @keyframes fall {
            0% {
                opacity: 0;
                transform: translateY(-10vh) rotate(0deg);
            }
            10% {
                opacity: 0.8;
            }
            90% {
                opacity: 0.5;
            }
            100% {
                opacity: 0;
                transform: translateY(110vh) rotate(360deg);
            }
        }

        .container {
            position: relative;
            z-index: 10;
            max-width: 900px;
            margin: 0 auto;
            padding: 60px 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
            opacity: 0;
            animation: fadeInDown 1.5s ease forwards;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rose-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(-5deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--rose-pink), var(--gold), var(--rose-red));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .subtitle {
            font-size: 1.3rem;
            color: var(--rose-pink);
            font-style: italic;
            letter-spacing: 3px;
        }

        .card {
            background: rgba(255, 245, 245, 0.05);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 107, 157, 0.2);
            border-radius: 25px;
            padding: 50px 40px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(196, 30, 58, 0.3);
            opacity: 0;
            animation: fadeInUp 1.5s ease forwards;
            animation-delay: 0.5s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .journey {
            font-size: 1.8rem;
            line-height: 1.9;
            text-align: center;
            margin-bottom: 35px;
        }

        .journey-highlight {
            color: var(--gold);
            font-weight: bold;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
        }

        .message {
            font-size: 1.4rem;
            line-height: 2;
            text-align: justify;
            margin-bottom: 30px;
            color: rgba(255, 245, 245, 0.95);
        }

        .message p {
            margin-bottom: 25px;
        }

        .highlight {
            color: var(--rose-pink);
            font-weight: bold;
            font-style: italic;
        }

        .code-block {
            background: rgba(0, 0, 0, 0.4);
            border-left: 4px solid var(--rose-red);
            padding: 25px;
            margin: 30px 0;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            line-height: 1.8;
            overflow-x: auto;
            opacity: 0;
            animation: fadeInUp 1.5s ease forwards;
            animation-delay: 1s;
        }

        .code-comment {
            color: var(--dark-green);
            filter: brightness(2);
        }

        .code-string {
            color: var(--rose-pink);
        }

        .code-keyword {
            color: var(--gold);
        }

        .signature {
            text-align: right;
            margin-top: 40px;
            font-size: 1.6rem;
            font-family: 'Playfair Display', serif;
            font-style: italic;
            color: var(--rose-pink);
            opacity: 0;
            animation: fadeInUp 1.5s ease forwards;
            animation-delay: 1.5s;
        }

        .footer-hearts {
            text-align: center;
            margin-top: 50px;
            font-size: 2.5rem;
            letter-spacing: 15px;
            opacity: 0;
            animation: fadeInUp 1.5s ease forwards;
            animation-delay: 2s;
        }

        .heart {
            display: inline-block;
            animation: heartbeat 1.5s ease infinite;
        }

        .heart:nth-child(2) {
            animation-delay: 0.2s;
        }

        .heart:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes heartbeat {
            0%, 100% {
                transform: scale(1);
            }
            10%, 30% {
                transform: scale(1.2);
            }
            20%, 40% {
                transform: scale(1.1);
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }
            
            .journey {
                font-size: 1.4rem;
            }
            
            .journey-highlight {
                font-size: 1.6rem;
            }
            
            .message {
                font-size: 1.2rem;
                text-align: left;
            }
            
            .code-block {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <!-- Falling petals -->
    <div class="petals" id="petals"></div>

    <div class="container">
        <div class="header">
            <div class="rose-icon">🌹</div>
            <h1>Happy Rose Day</h1>
            <div class="subtitle">Meri Zindagi, Meri Jaan</div>
        </div>

        <div class="card">
            <div class="journey">
                Har pal tumhare saath ✨
            </div>

            <div class="message">
                <p>Meri pyaari Biwi,</p>

                <p>Jab <span class="highlight">2014 mein BCA ke dino</span> mein pehli baar tumhe dekha tha, tab yeh nahi pata tha ki tum meri <span class="highlight">zindagi ki sabse khoobsurat khushi</span> ban jaogi. Tumhare bina meri duniya adhoori lagti hai, jaise <span class="highlight">chaand ke bina raat, suraj ke bina din</span>.</p>

                <p>Har subah tumhare saath uthna ek <span class="highlight">naya ehsaas</span> deta hai. Tumhari muskaan dekh kar lagta hai jaise <span class="highlight">saari duniya haseen</span> ho gayi ho. Tumhari aankhon mein jo pyaar hai, woh kisi bhi <span class="highlight">duniya ki daulat</span> se bhi kahi zyada kimti hai.</p>

                <p>Humne jo <span class="highlight">safar</span> shuru kiya tha college se, friendship se pyaar tak, phir relationship, aur ab yeh <span class="highlight">khubsurat bandhan</span> - har mod par tum saath thi. Har <span class="highlight">mushkil</span> ko humne saath mein jhela, har <span class="highlight">khushi</span> ko saath mein manaya. Tum mere dil ki <span class="highlight">dhadkan</span> ho, You're the reason for <span class="highlight">my existence</span>.</p>

                <p>Is <span class="highlight">Rose Day</span> par bas yahi kehna chahta hoon ki tum mere liye sirf ek rose nahi, balki ek poora <span class="highlight">baag</span> ho. Tumhari khushboo se meri zindagi mehak uthti hai. Tumhari hasi se mera ghar roshan ho jata hai.</p>
            </div>

            <div class="code-block" style="font-family: 'Crimson Text', serif; font-size: 1.3rem; font-style: italic; text-align: center; line-height: 2.2;">
<span style="color: var(--rose-pink); font-size: 1.5rem;">✨ Meri Jaan ✨</span><br><br>

Jab se tum mili ho,<br>
Har din ek <span style="color: var(--gold);">nayi subah</span> lagti hai<br><br>

Tumhari muskaan mein,<br>
Meri duniya ki <span style="color: var(--gold);">saari khushiyan</span> hain<br><br>

Tumhare saath ka har pal,<br>
Ek <span style="color: var(--gold);">khoobsurat yaad</span> ban jata hai<br><br>

Tumhare bina zindagi,<br>
Jaise <span style="color: var(--gold);">phoolon ke bina bahaar</span><br><br>

<span style="color: var(--rose-pink); font-size: 1.5rem;">Tum ho toh sab kuch hai ❤️</span>
            </div>

            <div class="message">
                <p>Tumhare saath bitaya har <span class="highlight">lamha</span> mere liye anmol hai. College ke wo din ho, relationship ke wo 3 saal ho, ya ab shaadi ke yeh <span class="highlight">2.5 mahine</span> - har pal yaadgaar hai kyunki <span class="highlight">tum saath ho</span>. Tumhare saath hasna, thoda ladna-jhagadna, phir manana... yeh sab meri zindagi ke sabse <span class="highlight">haseen pal</span> hain.</p>

                <p>Thank you for being meri <span class="highlight">sabse achi dost, meri life partner, aur meri sab kuch</span>. Thank you <span class="highlight">itne saalo</span> se saath dene ke liye, mujhe samajhne ke liye, aur mera haath thamne ke liye jab main kamzor pada.</p>

                <p>Main tumse utna hi pyaar karta hoon jitna ek <span class="highlight">parinda aasman se, ek machli paani se, ek phool khushboo se</span> karta hai. Tum meri <span class="highlight">zindagi, meri jaan, mera pyaar</span> ho. Hamesha rahogi. ❤️</p>

                <p>Shaadi ke baad bhi tum wahi ho - utni hi <span class="highlight">pyaari, utni hi haseen, utni hi meri</span>. Har din tumhare saath ek naya <span class="highlight">tohfa</span> lagta hai. I'm the luckiest person to have you as my wife. 💕</p>
            </div>

            <div class="signature">
                Sirf tumhara,<br>
                Tumhara Hamsafar 💕🌹
            </div>
        </div>

        <div class="footer-hearts">
            <span class="heart">💖</span>
            <span class="heart">🌹</span>
            <span class="heart">💖</span>
        </div>
    </div>

    <script>
        // Create falling petals
        const petalsContainer = document.getElementById('petals');
        const petalCount = 30;

        for (let i = 0; i < petalCount; i++) {
            const petal = document.createElement('div');
            petal.className = 'petal';
            petal.style.left = Math.random() * 100 + '%';
            petal.style.animationDuration = (Math.random() * 3 + 5) + 's';
            petal.style.animationDelay = Math.random() * 5 + 's';
            petal.style.width = (Math.random() * 8 + 6) + 'px';
            petal.style.height = petal.style.width;
            petalsContainer.appendChild(petal);
        }
    </script>
</body>
</html>