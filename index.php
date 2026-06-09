<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reviewon - Professional Feedback Platform</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/slideDown.css">
</head>
<body>
    <canvas id="particles-canvas" aria-hidden="true"></canvas>

    <?php include 'includes/header.php'; ?>


    <section class="hero">
        <div class="hero-content fade-in-up">
            <h1 class="hero-title">Unambiguous Feedback,<br>Faster Projects</h1>
            <p class="hero-subtitle">Professional tool for collecting team, client &amp; colleague feedback with high accuracy and incredible speed.</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number" data-target="10000">0</span>
                    <span class="stat-label">Feedbacks Collected</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-target="500">0</span>
                    <span class="stat-label">Active Projects</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" data-target="2500">0</span>
                    <span class="stat-label">Happy Users</span>
                </div>
            </div>
<button class="btn-primary hero-cta" id="heroLoginBtn">Get Started - Free</button>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title scroll-reveal">Key Features</h2>
            <div class="features-grid">
                <div class="feature-card scroll-reveal">
                    <div class="feature-icon"> <img src="image/flash.svg" alt="image" class="icon-feature"> </div>
                    <h3>Instant Feedback</h3>
                    <p>Click and feedback appears instantly. No delay, no complexity.</p>
                </div>
                <div class="feature-card fade-in-up" style="animation-delay: 0.1s;">
                    <div class="feature-icon"> <img src="image/monitor-mobbile.svg" alt="image" class="icon-feature"> </div>
                    <h3>Fully Responsive</h3>
                    <p>Mobile, tablet, desktop. Feedback anywhere.</p>
                </div>
                <div class="feature-card fade-in-up" style="animation-delay: 0.2s;">
                    <div class="feature-icon"> <img src="image/profile-2user.svg" alt="image" class="icon-feature"> </div>
                    <h3>Team Collaboration</h3>
                    <p>Easy sharing &amp; team work on feedback.</p>
                </div>
                <div></div>
                <div class="feature-card fade-in-up" style="animation-delay: 0.3s;">
                    <div class="feature-icon"> <img src="image/shield-tick.svg" alt="image" class="icon-feature"> </div>
                    <h3>Secure & Private</h3>
                    <p>Your data fully secure and confidential.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- <section id="why-us" class="why-us-section">
        <div class="container">
            <h2 class="section-title fade-in-up">Why Reviewon?</h2>
            <div class="why-us-grid">
                <div class="why-card fade-in-up">
                    <div class="why-number">01</div>
                    <h3>Infinite Simplicity</h3>
                    <p>No complex learning. Start in 30 seconds.</p>
                </div>
                <div class="why-card fade-in-up" style="animation-delay: 0.1s;">
                    <div class="why-number">02</div>
                    <h3>Lightning Speed</h3>
                    <p>Pages load instantly. No feedback waiting.</p>
                </div>
                <div class="why-card fade-in-up" style="animation-delay: 0.2s;">
                    <div class="why-number">03</div>
                    <h3>Beautiful Design</h3>
                    <p>Modern UI for delightful experience.</p>
                </div>
                <div class="why-card fade-in-up" style="animation-delay: 0.3s;">
                    <div class="why-number">04</div>
                    <h3>Full Support</h3>
                    <p>Always available to help you &amp; your team.</p>
                </div>
            </div>
        </div>
    </section> -->

    <?php include 'includes/footer.php'; ?>

    <script src="js/script.js"></script>
    <script>
        // Particles
        const canvas = document.getElementById('particles-canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        const particles = [];
        for(let i = 0; i < 80; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                radius: Math.random() * 2 + 1
            });
        }
        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => {
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(255,255,255,0.3)';
                ctx.fill();
                p.x += p.vx;
                p.y += p.vy;
                if(p.x < 0 || p.x > canvas.width) p.vx *= -1;
                if(p.y < 0 || p.y > canvas.height) p.vy *= -1;
            });
            requestAnimationFrame(animateParticles);
        }
        animateParticles();
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }); 

        // Stats Counters
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if(entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.stat-number');
                    counters.forEach(counter => {
                        const target = +counter.getAttribute('data-target');
                        const increment = target / 100;
                        let current = 0;
                        const update = () => {
                            if(current < target) {
                                current += increment;
                                counter.textContent = Math.floor(current).toLocaleString() + '+';
                                requestAnimationFrame(update);
                            } 
                            // else counter.textContent = target.toLocaleString() + '+';
                        };
                        update();
                    });
                }
            });
        });
        observer.observe(document.querySelector('.hero-stats'));
        
        // Scroll reveal for features
        const scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('revealed');
                    }, i * 150);
                }
            });
        });
        
        document.querySelectorAll('.scroll-reveal').forEach(el => scrollObserver.observe(el));
    </script>
</body>
</html>

