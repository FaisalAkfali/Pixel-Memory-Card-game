<?php
require_once '../includes/config.php';
require_once '../includes/classes/User.php';
require_once '../includes/classes/Game.php';

$user = new User();
if (!$user->isLoggedIn()) {
    redirect('login.php');
}

$username = $user->getUsername();
$userId = $user->getUserId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Pixel Memory | <?php echo e($username); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <div class="animated-bg"></div>

    <!-- Game Header -->
    <div style="position: fixed; top: 10px; left: 10px; background: #0b0f16ee; backdrop-filter: blur(4px); padding: 8px 15px; border: 2px solid #6b4c2c; display: flex; gap: 10px; z-index: 100; flex-wrap: wrap;">
        <span> <?php echo e($username); ?></span>
        <button id="saveScoreBtn" class="reset-btn"> SAVE SCORE</button>
        <a href="dashboard.php" class="reset-btn"> DASHBOARD</a>
        <a href="../actions/logout.php" class="reset-btn"> LOGOUT</a>
    </div>

    <div class="game-panel">
        <div class="stat-box"> MATCHES <span id="matchCounter">0</span> /<span id="totalPairs">12</span></div>
        <div class="stat-box"> MOVES <span id="moveCounter">0</span></div>
        <button class="reset-btn" id="resetGameBtn"> RESET</button>
        <button class="reset-btn" id="menuBtn"> MENU</button>
    </div>

    <div class="message-area" id="statusMessage">▶ CLICK A MODE BELOW TO START</div>

    <section class="memory-game" id="memoryGameGrid"></section>

    <div id="startScreen" class="start-overlay">
        <div class="menu-box">
            <h1 class="pixel-title">PIXEL MEMORY</h1>
            <div class="menu-box-nav flex" style="justify-content: center; flex-wrap: wrap; gap: 10px; margin-bottom: 22px;">
                <a href="../index.php" class="reset-btn">HOME</a>
                <a href="dashboard.php" class="reset-btn">DASHBOARD</a>
                <?php if ($user->isAdmin()): ?>
                <a href="admin.php" class="reset-btn">ADMIN</a>
                <?php endif; ?>
            </div>
            <div class="mode-options">
                <button class="mode-btn" data-mode="off">
                    <span class="mode-icon">🕹️</span> 
                    <div>CLASSIC <small>Solo practice</small></div>
                </button>
                <button class="mode-btn challenge" data-mode="challenge">
                    <span class="mode-icon">🤖</span> 
                    <div>CHALLENGE <small>VS AI Opponent</small></div>
                </button>
                <button class="mode-btn helper" data-mode="helper">
                    <span class="mode-icon">🧚</span> 
                    <div>HELPER <small>AI gives hints</small></div>
                </button>
                <button class="mode-btn sabotage" data-mode="sabotage">
                    <span class="mode-icon">😈</span> 
                    <div>SABOTAGE <small>AI shuffles cards</small></div>
                </button>
            </div>
        </div>
    </div>

    <div class="pixel-corners"></div>

    <script>
        const currentUserId = <?php echo $userId; ?>;
        const currentUsername = "<?php echo e($username); ?>";
        
        const PAIR_DEFINITIONS = [
            { framework: "Clubs Ace", display: " ACE", img: "../assets/images/Cards/Clubs/Clubs Ace Card.png" },
            { framework: "Clubs King", display: " KING", img: "../assets/images/Cards/Clubs/Clubs King Card.png" },
            { framework: "Clubs Queen", display: " QUEEN", img: "../assets/images/Cards/Clubs/Clubs Queen Card.png" },
            { framework: "Clubs Jack", display: " JACK", img: "../assets/images/Cards/Clubs/Clubs Jack Card.png" },
            { framework: "Diamonds Ace", display: " ACE", img: "../assets/images/Cards/Diamonds/Diamond Ace Card.png" },
            { framework: "Diamonds King", display: " KING", img: "../assets/images/Cards/Diamonds/Diamond King Card.png" },
            { framework: "Diamonds Queen", display: " QUEEN", img: "../assets/images/Cards/Diamonds/Diamond Queen Card.png" },
            { framework: "Diamonds Jack", display: " JACK", img: "../assets/images/Cards/Diamonds/Diamond Jack Card.png" },
            { framework: "Clubs Ten", display: " 10", img: "../assets/images/Cards/Clubs/Clubs of 10 Card.png" },
            { framework: "Diamonds Ten", display: " 10", img: "../assets/images/Cards/Diamonds/Diamond of 10 Card.png" },
            { framework: "Hearts Ten", display: " 10", img: "../assets/images/Cards/Hearts/Heart of 10 Card.png" },
            { framework: "Spades Ten", display: " 10", img: "../assets/images/Cards/Spades/Spades of 10 Card.png" }
        ];

        let currentCards = [];
        let lockBoard = false;
        let firstCard = null;
        let secondCard = null;
        let moves = 0;
        let matchedPairs = 0;
        let aiMode = 'off';
        let aiMemory = [];
        let challengeAIRound = false;
        let openingPeekTimer = null;
        let totalPairs = PAIR_DEFINITIONS.length;

        let gameContainer;
        let matchCounterSpan;
        let moveCounterSpan;
        let totalPairsSpan;
        let statusMsgDiv;

        window.moves = moves;
        window.matchedPairs = matchedPairs;
        window.aiMode = aiMode;
        window.totalPairs = totalPairs;

        function shuffleArray(arr) {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
            return arr;
        }

        function createDeck() {
            let deck = [];
            PAIR_DEFINITIONS.forEach(pair => {
                const sharedData = { framework: pair.framework, img: pair.img, matched: false, flipped: false };
                deck.push({ ...sharedData, id: crypto.randomUUID ? crypto.randomUUID() : Math.random() + '-a' });
                deck.push({ ...sharedData, id: crypto.randomUUID ? crypto.randomUUID() : Math.random() + '-b' });
            });
            return shuffleArray(deck);
        }

        function updateUIStats() {
            if (matchCounterSpan) matchCounterSpan.textContent = matchedPairs;
            if (moveCounterSpan) moveCounterSpan.textContent = moves;
            window.moves = moves;
            window.matchedPairs = matchedPairs;
        }

        function setStatusMessage(msg, isError = false) {
            if (statusMsgDiv) {
                statusMsgDiv.textContent = msg;
                statusMsgDiv.style.borderLeftColor = isError ? "#e74c3c" : "#f0b27a";
            }
        }

        function renderGameGrid() {
    if (!gameContainer) return;
    gameContainer.innerHTML = "";
    if (!currentCards || currentCards.length === 0) return;
    
    currentCards.forEach((card) => {
        const cardDiv = document.createElement("div");
        cardDiv.className = `memory-card ${card.flipped ? 'flip' : ''} ${card.matched ? 'matched' : ''}`;
        cardDiv.setAttribute("data-id", card.id);

        const frontDiv = document.createElement("div");
        frontDiv.className = "front-face";
        
        // FORCE IMAGE TO FILL CARD
        const imgFront = document.createElement("img");
        imgFront.src = card.img;
        imgFront.style.width = "100%";
        imgFront.style.height = "100%";
        imgFront.style.objectFit = "cover";
        imgFront.style.position = "absolute";
        imgFront.style.top = "0";
        imgFront.style.left = "0";
        
        imgFront.onerror = function() {
            this.style.display = "none";
            const fallback = document.createElement("div");
            fallback.style.width = "100%";
            fallback.style.height = "100%";
            fallback.style.display = "flex";
            fallback.style.alignItems = "center";
            fallback.style.justifyContent = "center";
            fallback.style.fontSize = "2rem";
            fallback.style.background = "#fcf6e8";
            fallback.textContent = card.display || "🎴";
            frontDiv.appendChild(fallback);
        };
        
        frontDiv.appendChild(imgFront);

        const backDiv = document.createElement("div");
        backDiv.className = "back-face";
        const imgBack = document.createElement("img");
        imgBack.src = "../assets/images/Cards/Back_Face_Card/Back Face Card.png";
        imgBack.style.width = "100%";
        imgBack.style.height = "100%";
        imgBack.style.objectFit = "cover";
        imgBack.style.position = "absolute";
        imgBack.style.top = "0";
        imgBack.style.left = "0";
        
        imgBack.onerror = function() {
            this.style.display = "none";
            const fallback = document.createElement("div");
            fallback.style.width = "100%";
            fallback.style.height = "100%";
            fallback.style.display = "flex";
            fallback.style.alignItems = "center";
            fallback.style.justifyContent = "center";
            fallback.style.fontSize = "2rem";
            fallback.style.background = "#2d2b1f";
            fallback.style.color = "#8b6914";
            fallback.textContent = "★";
            backDiv.appendChild(fallback);
        };
        
        backDiv.appendChild(imgBack);
        cardDiv.appendChild(frontDiv);
        cardDiv.appendChild(backDiv);

        cardDiv.addEventListener("click", () => {
            if (lockBoard || card.matched) return;

            if (aiMode === 'challenge' && firstCard && !secondCard && card.id === firstCard.id && card.flipped) {
                const cardObj = currentCards.find(c => c.id === card.id);
                if (cardObj) {
                    cardObj.flipped = false;
                    aiMemory = aiMemory.filter(m => m.id !== card.id);
                }
                cardDiv.classList.remove("flip");
                firstCard = null;
                challengeAIRound = false;
                setStatusMessage("↩ Pick another card — still your turn!");
                return;
            }

            if (card.flipped) return;
            if (aiMode === 'challenge') challengeAIRound = false;
            flipCardLogic(card.id, cardDiv);
        });

        gameContainer.appendChild(cardDiv);
    });
    updateMemoryGridLayout();
}

        function clearOpeningPeekTimer() {
            if (openingPeekTimer !== null) {
                clearTimeout(openingPeekTimer);
                openingPeekTimer = null;
            }
        }

        /** Shows every unmatched card face-up briefly (does not use flipCardLogic — AI memory stays fair). */
        function runOpeningPeek(durationMs) {
            clearOpeningPeekTimer();
            if (!gameContainer || !currentCards.length) return;
            lockBoard = true;
            firstCard = null;
            secondCard = null;
            currentCards.forEach(c => {
                if (!c.matched) c.flipped = true;
            });
            renderGameGrid();
            setStatusMessage("👀 Memorize the board — cards hide in " + (durationMs / 1000) + "s...");

            openingPeekTimer = setTimeout(() => {
                openingPeekTimer = null;
                currentCards.forEach(c => {
                    if (!c.matched) c.flipped = false;
                });
                lockBoard = false;
                renderGameGrid();
                const label = (aiMode && aiMode !== "off") ? aiMode.toUpperCase() : "CLASSIC";
                setStatusMessage("▶ MODE: " + label + " — pick two cards!");
            }, durationMs);
        }

        function updateMemoryGridLayout() {
            if (!gameContainer) return;
            const n = currentCards.length;
            if (!n) return;
            const vw = window.innerWidth;
            let cols = 4;
            if (vw < 600) {
                cols = 2;
            } else if (vw < 1100) {
                cols = 4;
            } else {
                if (n % 6 === 0 && n / 6 <= 10) cols = 6;
                else if (n % 8 === 0 && n / 8 <= 8) cols = 8;
                else cols = 4;
            }
            const rows = Math.ceil(n / cols);
            gameContainer.style.setProperty('--grid-cols', String(cols));
            gameContainer.style.setProperty('--grid-rows', String(rows));
        }

        function flipCardLogic(cardId, cardElement) {
            const cardObj = currentCards.find(c => c.id === cardId);
            if (!cardObj) return;
            
            cardObj.flipped = true;
            cardElement.classList.add("flip");

            if (!aiMemory.find(m => m.id === cardId)) {
                aiMemory.push({ id: cardId, framework: cardObj.framework });
            }

            if (!firstCard) {
                firstCard = { id: cardId, framework: cardObj.framework, element: cardElement };
                handleHelperAI();
            } else if (!secondCard && firstCard.id !== cardId) {
                secondCard = { id: cardId, framework: cardObj.framework, element: cardElement };
                moves++;
                updateUIStats();
                checkMatch();
            }
        }

        function checkMatch() {
            const isMatch = firstCard.framework === secondCard.framework;
            lockBoard = true;

            if (isMatch) {
                const c1 = currentCards.find(c => c.id === firstCard.id);
                const c2 = currentCards.find(c => c.id === secondCard.id);
                if (c1) c1.matched = true;
                if (c2) c2.matched = true;
                firstCard.element.classList.add("matched");
                secondCard.element.classList.add("matched");
                matchedPairs++;
                updateUIStats();
                
                firstCard = null;
                secondCard = null;
                lockBoard = false;

                if (matchedPairs === totalPairs) {
                    setStatusMessage("🏆 YOU WIN! 🏆 Click SAVE SCORE to record!");
                    showCelebration();
                } else {
                    if (aiMode === 'challenge') {
                        if (challengeAIRound) {
                            handlePostMoveAI();
                        } else {
                            setStatusMessage("✨ You matched! Pick two more cards.");
                        }
                    } else {
                        handlePostMoveAI();
                    }
                }
            } else {
                firstCard.element.classList.add("mismatch");
                secondCard.element.classList.add("mismatch");
                
                setTimeout(() => {
                    const c1 = currentCards.find(c => c.id === firstCard.id);
                    const c2 = currentCards.find(c => c.id === secondCard.id);
                    if (c1) c1.flipped = false;
                    if (c2) c2.flipped = false;
                    firstCard.element.classList.remove("flip", "mismatch");
                    secondCard.element.classList.remove("flip", "mismatch");
                    firstCard = null;
                    secondCard = null;
                    lockBoard = false;
                    if (aiMode === 'challenge' && challengeAIRound) {
                        challengeAIRound = false;
                        setStatusMessage("🕹️ YOUR TURN!");
                    } else {
                        handlePostMoveAI();
                    }
                }, 800);
            }
        }

        function showCelebration() {
            for(var i = 0; i < 30; i++) {
                var confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.width = '8px';
                confetti.style.height = '8px';
                confetti.style.background = 'hsl(' + Math.random() * 360 + ', 100%, 50%)';
                confetti.style.animation = 'fall ' + (Math.random() * 3 + 2) + 's linear';
                confetti.style.zIndex = '9999';
                document.body.appendChild(confetti);
                setTimeout(function(c) { c.remove(); }, 3000);
            }
        }

        function handleHelperAI() {
            if (aiMode !== 'helper' || !firstCard) return;
            
            const match = aiMemory.find(m => m.framework === firstCard.framework && m.id !== firstCard.id);
            if (match) {
                const el = document.querySelector(`[data-id="${match.id}"]`);
                if (el) {
                    el.style.filter = "brightness(1.5) drop-shadow(0 0 10px yellow)";
                    setTimeout(() => el.style.filter = "", 800);
                }
                setStatusMessage("🧚 HELPER: I remember this one!");
            }
        }

        function handlePostMoveAI() {
            if (aiMode === 'challenge' && !lockBoard && matchedPairs < totalPairs) {
                lockBoard = true;
                setStatusMessage("🤖 AI'S TURN...");
                setTimeout(runChallengeAI, 1000);
            } else if (aiMode === 'sabotage' && moves > 0 && moves % 3 === 0 && !lockBoard && matchedPairs < totalPairs) {
                triggerSabotage();
            }
        }

        function runChallengeAI() {
            if (matchedPairs === totalPairs) {
                lockBoard = false;
                return;
            }
            
            let card1, card2;
            const pair = findMatchInAIMemory();
            const available = currentCards.filter(c => !c.matched && !c.flipped);
            
            if (available.length < 2) {
                lockBoard = false;
                return;
            }

            if (pair && pair[0] && pair[1]) {
                card1 = pair[0];
                card2 = pair[1];
            } else {
                const randomIndex1 = Math.floor(Math.random() * available.length);
                card1 = available[randomIndex1];
                let available2 = available.filter(c => c.id !== card1.id);
                const randomIndex2 = Math.floor(Math.random() * available2.length);
                card2 = available2[randomIndex2];
            }

            const el1 = document.querySelector(`[data-id="${card1.id}"]`);
            const el2 = document.querySelector(`[data-id="${card2.id}"]`);
            
            if (el1 && el2) {
                challengeAIRound = true;
                setTimeout(() => {
                    flipCardLogic(card1.id, el1);
                    setTimeout(() => {
                        flipCardLogic(card2.id, el2);
                        setTimeout(() => {
                            lockBoard = false;
                        }, 500);
                    }, 500);
                }, 300);
            } else {
                lockBoard = false;
            }
        }

        function triggerSabotage() {
            setStatusMessage("😈 SABOTAGE: SHUFFLING BOARD!");
            
            const unmatched = currentCards.filter(c => !c.matched);
            if (unmatched.length > 0) {
                const shuffled = shuffleArray([...unmatched]);
                let i = 0;
                currentCards = currentCards.map(c => c.matched ? c : shuffled[i++]);
                
                currentCards.forEach(c => { c.flipped = !!c.matched; });
                firstCard = null;
                secondCard = null;
                
                renderGameGrid();
            }
        }

        function findMatchInAIMemory() {
            for (let i = 0; i < aiMemory.length; i++) {
                for (let j = i + 1; j < aiMemory.length; j++) {
                    if (aiMemory[i].framework === aiMemory[j].framework) {
                        const c1 = currentCards.find(c => c.id === aiMemory[i].id);
                        const c2 = currentCards.find(c => c.id === aiMemory[j].id);
                        if (c1 && c2 && !c1.matched && !c2.matched) {
                            return [c1, c2];
                        }
                    }
                }
            }
            return null;
        }

        function startGame(mode) {
            aiMode = mode;
            window.aiMode = mode;
            aiMemory = [];
            
            const startScreen = document.getElementById("startScreen");
            if (startScreen) {
                startScreen.classList.add("hidden");
            }
            
            resetGame();
        }

        function resetGame() {
            clearOpeningPeekTimer();
            lockBoard = false;
            firstCard = null;
            secondCard = null;
            moves = 0;
            matchedPairs = 0;
            aiMemory = [];
            challengeAIRound = false;
            
            updateUIStats();
            currentCards = createDeck();
            runOpeningPeek(1000);
        }

        document.addEventListener("DOMContentLoaded", function() {
            gameContainer = document.getElementById("memoryGameGrid");
            matchCounterSpan = document.getElementById("matchCounter");
            moveCounterSpan = document.getElementById("moveCounter");
            totalPairsSpan = document.getElementById("totalPairs");
            statusMsgDiv = document.getElementById("statusMessage");
            
            if (totalPairsSpan) {
                totalPairsSpan.textContent = totalPairs;
            }

            let gridLayoutResizeTimer;
            window.addEventListener("resize", function() {
                clearTimeout(gridLayoutResizeTimer);
                gridLayoutResizeTimer = setTimeout(updateMemoryGridLayout, 120);
            });
        });

        window.startGame = startGame;
        window.resetGame = resetGame;
    </script>

    <script>
        var style = document.createElement('style');
        style.textContent = '@keyframes fall { 0% { transform: translateY(0) rotate(0deg); opacity: 1; } 100% { transform: translateY(100vh) rotate(360deg); opacity: 0; } }';
        document.head.appendChild(style);
        
        $(document).ready(function() {
            $('.mode-btn').click(function() {
                var mode = $(this).data('mode');
                startGame(mode);
            });
            
            $('#resetGameBtn').click(function() {
                resetGame();
            });
            
            $('#menuBtn').click(function() {
                location.reload();
            });
            
            $('.memory-card').on('mouseenter', function() {
                $(this).css('transform', 'scale(1.02)');
            }).on('mouseleave', function() {
                $(this).css('transform', 'scale(1)');
            });
            
            $('#saveScoreBtn').click(function() {
                if (typeof window.moves !== 'undefined' && typeof window.matchedPairs !== 'undefined' && typeof window.aiMode !== 'undefined') {
                    if (window.matchedPairs === window.totalPairs) {
                        $.ajax({
                            url: '../actions/save-score.php',
                            method: 'POST',
                            data: {
                                moves: window.moves,
                                matched_pairs: window.matchedPairs,
                                game_mode: window.aiMode,
                                user_id: currentUserId,
                                csrf_token: '<?php echo generateCSRFToken(); ?>'
                            },
                            success: function(response) {
                                try {
                                    const result = JSON.parse(response);
                                    if(result.success) {
                                        $('#statusMessage').text(' Score saved! ' + result.message);
                                    } else {
                                        $('#statusMessage').text(' ' + result.message);
                                    }
                                } catch(e) {
                                    $('#statusMessage').text(' Error saving score');
                                }
                            }
                        });
                    } else {
                        $('#statusMessage').text(' Complete the game first! Match all ' + window.totalPairs + ' pairs.');
                    }
                } else {
                    $('#statusMessage').text(' Start a game first!');
                }
            });
        });
    </script>

    <script>
        const bgElement = document.querySelector('.animated-bg');
        if(bgElement) {
            for(let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'pixel-particle';
                particle.style.width = Math.random() * 4 + 2 + 'px';
                particle.style.height = particle.style.width;
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 8 + 's';
                particle.style.animationDuration = Math.random() * 6 + 4 + 's';
                particle.style.background = `hsl(${Math.random() * 60 + 100}, 70%, 55%)`;
                bgElement.appendChild(particle);
            }
        }
    </script>
</body>
</html>