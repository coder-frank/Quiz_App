<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel Quiz</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('home.css') }}">
</head>

<body>
    <!-- Floating Question Marks -->
     <!-- Floating Animated Icons -->
     <div class="floating-icons">
        <div class="icon">🧠</div>
        <div class="icon">?</div>
        <div class="icon">📖</div>
        <div class="icon">?</div>
        <div class="icon">🧠</div>
        <div class="icon">📖</div>
        <div class="icon">?</div>
        <div class="icon">🧠</div>
        <div class="icon">?</div>
        <div class="icon">📖</div>
    </div>

    <div class="container">
        <!-- Welcome Box -->
        <div class="welcome-box">
            <h1>Welcome to the Inter-Faculty <span class="highlight">Knowledge Knockout</span></h1>
            <h2>Battle of the Brains</h2>
            <p>Get ready for an exciting quiz competition!</p>
            <a href="/quiz/1" class="btn">Start Quiz</a>
        </div>

        <!-- Developer Section -->
        <div class="developer-container">
            <div class="developer-card">
                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Developer 1">
                <h3>Akpan Emmanuel</h3>
                <p>Lead Developer</p>
            </div>
            <div class="developer-card">
                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Developer 2">
                <h3>Frank Emmanuel</h3>
                <p>Fullstack Developer <br>(KODA INNOVATIONS)</p>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const devCards = document.querySelectorAll(".developer-card");
            devCards.forEach(card => {
                card.addEventListener("mouseenter", () => {
                    card.classList.add("hovered");
                });
                card.addEventListener("mouseleave", () => {
                    card.classList.remove("hovered");
                });
            });
        });
    </script>
</body>

</html>
