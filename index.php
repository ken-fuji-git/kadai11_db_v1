<?php
session_start();
require_once __DIR__ . '/funcs.php';
sschk();

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ペンギン会議</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div id="app">
        <a class="navbar-brand" href="logout.php">ログアウト</a>

        <header>
            <h1>今日のペンギン会議</h1>
            <p class="subtitle">今日のがんばりを、ペンギンたちとお話ししませんか？</p>
        </header>

        <main>
            <!-- Stage Area: Where penguins gather -->
            <div id="penguin-stage" class="stage-container">
                <div class="penguin-group">
                    <!-- Penguins will be dynamically added here via JS -->
                    <div class="penguin placeholder hidden">🐧</div>
                </div>

                <!-- Chat Overlay: Floats over the stage -->
                <div id="chat-overlay" class="chat-container">
                    <!-- Messages will appear here -->
                </div>
            </div>

            <!-- Input Area: User types here -->
            <div id="input-area" class="input-container">
                <label for="diary-input">今日あったこと、がんばったことは？</label>
                <textarea id="diary-input" placeholder="例：今日は朝起きるのが辛かったけど、なんとか起きて出社した！..." rows="4"></textarea>
                <button id="start-btn" class="main-btn">会議を始める</button>
            </div>
        </main>
    </div>
    <script src="js/app.js"></script>
</body>

</html>