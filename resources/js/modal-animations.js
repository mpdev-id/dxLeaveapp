
import gsap from 'gsap';

// GSAP-powered modal open function
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with id ${modalId} not found.`);
        return;
    }

    // Ensure the modal is ready to be shown
    modal.showModal();

    const modalBox = modal.querySelector('.modal-box');
    const modalBackdrop = modal.parentElement.querySelector('.modal-backdrop');

    // Animation timeline
    const tl = gsap.timeline();
    tl.fromTo(modalBackdrop, { opacity: 0 }, { opacity: 1, duration: 0.3, ease: 'power2.out' })
      .fromTo(modalBox, { opacity: 0, scale: 0.95, y: -20 }, { opacity: 1, scale: 1, y: 0, duration: 0.3, ease: 'power2.out' }, "-=0.2");
}

// GSAP-powered modal close function
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with id ${modalId} not found.`);
        return;
    }

    const modalBox = modal.querySelector('.modal-box');
    const modalBackdrop = modal.parentElement.querySelector('.modal-backdrop');

    // Animation timeline
    const tl = gsap.timeline({
        onComplete: () => {
            modal.close(); // Close the dialog after animation
        }
    });
    tl.to(modalBox, { opacity: 0, scale: 0.95, y: 20, duration: 0.2, ease: 'power2.in' })
      .to(modalBackdrop, { opacity: 0, duration: 0.2, ease: 'power2.in' }, "-=0.1");
}

// Expose functions to the global scope to be used in Blade files
window.showModal = showModal;
window.hideModal = hideModal;
