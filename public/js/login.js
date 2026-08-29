/**
 * METRIC — Vibrant Background Particle Physics Engine & Login Script
 * Full-Viewport moving particles with constellation lines and magnetic mouse reactivity
 */

function initLoginModule() {
    // 1. Initialize Fullscreen Background Particle Canvas
    initFullscreenParticles();

    // 2. Form Submission Loading Animation
    const loginForm = document.getElementById('loginAuthForm');
    const submitBtn = document.getElementById('btnSubmitLogin');

    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', () => {
            const btnText = document.getElementById('btnSubmitText');
            const btnSpinner = document.getElementById('btnSubmitSpinner');
            
            if (btnText && btnSpinner) {
                btnText.style.display = 'none';
                btnSpinner.style.display = 'inline-flex';
            }
            submitBtn.style.opacity = '0.9';
            submitBtn.style.pointerEvents = 'none';
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLoginModule);
} else {
    initLoginModule();
}

/**
 * Fullscreen Interactive Particle Network
 */
function initFullscreenParticles() {
    const canvas = document.getElementById('particlesCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    function handleResize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        createParticles();
    }

    window.addEventListener('resize', handleResize);

    let particles = [];
    const particleCount = Math.max(50, Math.min(Math.floor((width * height) / 10000), 95));

    const colors = [
        { r: 16, g: 185, b: 223 },   // Cyan #10b9df
        { r: 8, g: 150, b: 181 },    // Teal #0896b5
        { r: 145, g: 197, b: 27 },   // Lime #91c51b
        { r: 80, g: 215, b: 245 }    // Bright Cyan
    ];

    const mouse = {
        x: null,
        y: null,
        radius: 160
    };

    window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    });

    window.addEventListener('mouseleave', () => {
        mouse.x = null;
        mouse.y = null;
    });

    class Particle {
        constructor() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;
            this.vx = (Math.random() - 0.5) * 0.5;
            this.vy = (Math.random() - 0.5) * 0.5;
            if (Math.abs(this.vx) < 0.12) this.vx = this.vx < 0 ? -0.18 : 0.18;
            if (Math.abs(this.vy) < 0.12) this.vy = this.vy < 0 ? -0.18 : 0.18;

            this.radius = Math.random() * 3.5 + 2;
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.alpha = Math.random() * 0.45 + 0.45;
            this.pulseSpeed = Math.random() * 0.015 + 0.008;
            this.pulseVal = Math.random() * Math.PI * 2;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;
            this.pulseVal += this.pulseSpeed;

            // Bounce off edges smoothly
            if (this.x < 0) { this.x = 0; this.vx *= -1; }
            if (this.x > width) { this.x = width; this.vx *= -1; }
            if (this.y < 0) { this.y = 0; this.vy *= -1; }
            if (this.y > height) { this.y = height; this.vy *= -1; }

            // Gentle mouse proximity repulsion
            if (mouse.x !== null && mouse.y !== null) {
                const dx = mouse.x - this.x;
                const dy = mouse.y - this.y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < mouse.radius && dist > 0) {
                    const force = (mouse.radius - dist) / mouse.radius;
                    this.x -= (dx / dist) * force * 2.2;
                    this.y -= (dy / dist) * force * 2.2;
                }
            }
        }

        draw() {
            const dynamicAlpha = Math.min(1, Math.max(0.35, this.alpha + Math.sin(this.pulseVal) * 0.3));
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, ${dynamicAlpha})`;
            ctx.shadowColor = `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 0.95)`;
            ctx.shadowBlur = 14;
            ctx.fill();
            ctx.shadowBlur = 0;
        }
    }

    function createParticles() {
        particles = [];
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }
    }

    function connectParticles() {
        const maxDist = 140;
        for (let a = 0; a < particles.length; a++) {
            for (let b = a + 1; b < particles.length; b++) {
                const dx = particles[a].x - particles[b].x;
                const dy = particles[a].y - particles[b].y;
                const dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < maxDist) {
                    const opacity = (1 - dist / maxDist) * 0.38;
                    ctx.beginPath();
                    const grad = ctx.createLinearGradient(particles[a].x, particles[a].y, particles[b].x, particles[b].y);
                    grad.addColorStop(0, `rgba(${particles[a].color.r}, ${particles[a].color.g}, ${particles[a].color.b}, ${opacity})`);
                    grad.addColorStop(1, `rgba(${particles[b].color.r}, ${particles[b].color.g}, ${particles[b].color.b}, ${opacity})`);
                    ctx.strokeStyle = grad;
                    ctx.lineWidth = 1.3;
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(particles[b].x, particles[b].y);
                    ctx.stroke();
                }
            }

            // Connection to mouse cursor
            if (mouse.x !== null && mouse.y !== null) {
                const dx = particles[a].x - mouse.x;
                const dy = particles[a].y - mouse.y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 170 && dist > 0) {
                    const opacity = (1 - dist / 170) * 0.6;
                    ctx.beginPath();
                    ctx.strokeStyle = `rgba(16, 185, 223, ${opacity})`;
                    ctx.lineWidth = 1.5;
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(mouse.x, mouse.y);
                    ctx.stroke();
                }
            }
        }
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();
        }

        connectParticles();
        requestAnimationFrame(animate);
    }

    createParticles();
    animate();
}

/**
 * Toggle Password Visibility (Show / Hide)
 * @param {string} inputId 
 * @param {HTMLElement} btn 
 */
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';

    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');

    if (eyeOpen && eyeClosed) {
        eyeOpen.style.display = isPassword ? 'none' : 'block';
        eyeClosed.style.display = isPassword ? 'block' : 'none';
    }
}
