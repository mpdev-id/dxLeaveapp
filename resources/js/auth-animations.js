
import gsap from 'gsap';

document.addEventListener('DOMContentLoaded', () => {
    // Animated Background
    const bgContainer = document.getElementById('animated-bg');
    if (bgContainer) {
        const squareCount = 30;
        const fragment = document.createDocumentFragment();
        for (let i = 0; i < squareCount; i++) {
            const square = document.createElement('div');
            square.className = 'bg-square';
            gsap.set(square, {
                x: Math.random() * window.innerWidth,
                y: Math.random() * window.innerHeight,
                width: Math.random() * 50 + 10,
                height: square.width,
                opacity: Math.random() * 0.5 + 0.1,
                backgroundColor: '#fff',
            });
            fragment.appendChild(square);
        }
        bgContainer.appendChild(fragment);

        gsap.to('.bg-square', {
            duration: 20,
            x: 'random(-100, 100)',
            y: 'random(-100, 100)',
            ease: 'sine.inOut',
            repeat: -1,
            yoyo: true,
            stagger: 0.1,
        });
    }

    // Form Animation
    const authCard = document.querySelector('.auth-card');
    if (authCard) {
        gsap.from(authCard, {
            duration: 0.8,
            opacity: 0,
            y: 50,
            ease: 'power2.out',
            delay: 0.3
        });

        gsap.from(authCard.querySelectorAll('h1, .form-control, .btn'), {
            duration: 0.6,
            opacity: 0,
            y: 20,
            stagger: 0.1,
            ease: 'power2.out',
            delay: 0.5
        });
    }
});
