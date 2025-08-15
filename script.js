// Mobile Menu Toggle
const menuToggle = document.getElementById('menuToggle');
const sideMenu = document.getElementById('sideMenu');
const closeMenu = document.getElementById('closeMenu');
const body = document.body;

function toggleMenu() {
    sideMenu.classList.toggle('active');
    body.classList.toggle('menu-open');
    
    // Create overlay if it doesn't exist
    if (!document.querySelector('.menu-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'menu-overlay';
        overlay.onclick = closeMenuHandler;
        document.body.appendChild(overlay);
    }
    
    const overlay = document.querySelector('.menu-overlay');
    overlay.classList.toggle('active');
}

function closeMenuHandler() {
    sideMenu.classList.remove('active');
    body.classList.remove('menu-open');
    const overlay = document.querySelector('.menu-overlay');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

menuToggle.addEventListener('click', toggleMenu);
closeMenu.addEventListener('click', closeMenuHandler);

// Close menu when clicking on side menu links
document.querySelectorAll('.side-menu-link').forEach(link => {
    link.addEventListener('click', closeMenuHandler);
});

// Header scroll effect
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Catalog filters with dropdown functionality
const filterButtons = document.querySelectorAll('.filter-btn');
const dropdownBtns = document.querySelectorAll('.dropdown-btn');
const seminarCards = document.querySelectorAll('.seminar-card');

// Handle dropdown toggles
dropdownBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const dropdown = btn.nextElementSibling;
        const isActive = dropdown.classList.contains('show');
        
        // Close all dropdowns
        document.querySelectorAll('.dropdown-content').forEach(d => {
            d.classList.remove('show');
        });
        document.querySelectorAll('.dropdown-btn').forEach(d => {
            d.classList.remove('active');
        });
        
        // Toggle current dropdown
        if (!isActive) {
            dropdown.classList.add('show');
            btn.classList.add('active');
        }
    });
});

// Close dropdowns when clicking outside
document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-content').forEach(dropdown => {
        dropdown.classList.remove('show');
    });
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.classList.remove('active');
    });
});

// Handle filter functionality
filterButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        button.classList.add('active');
        
        const category = button.dataset.category;
        
        // Filter seminar cards
        seminarCards.forEach(card => {
            const cardCategories = card.dataset.category.split(' ');
            
            if (category === 'all' || 
                cardCategories.includes(category) || 
                (category === 'ai' && cardCategories.some(cat => cat.startsWith('ai-'))) ||
                (category === 'komunikace' && cardCategories.some(cat => cat.startsWith('komunikace-'))) ||
                (category === 'psychologie' && cardCategories.some(cat => cat.startsWith('psychologie-')))) {
                card.style.display = 'block';
                card.style.animation = 'fadeInUp 0.6s ease-out';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Close dropdown if it was clicked inside
        const dropdown = button.closest('.dropdown-content');
        if (dropdown) {
            dropdown.classList.remove('show');
            dropdown.previousElementSibling.classList.remove('active');
        }
    });
});

// Booking form handling with PHP mailer
const bookingForm = document.getElementById('bookingForm');

bookingForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Disable submit button during processing
    const submitBtn = bookingForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Odesílám...';
    
    // Get form data
    const formData = new FormData(bookingForm);
    const data = {};
    
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    console.log('Odesílám data:', data); // Debug log
    
    try {
        // Send data to PHP endpoint (now secured)
        const response = await fetch('send_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status); // Debug log
        console.log('Response headers:', response.headers); // Debug log
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Response result:', result); // Debug log
        
        if (result.success) {
            // Show success message
            showNotification(result.message, 'success');
            // Reset form
            bookingForm.reset();
        } else {
            // Show error message
            showNotification(result.message || 'Nastala chyba při odesílání formuláře.', 'error');
            
            // Log debug info if available
            if (result.debug) {
                console.error('Debug info:', result.debug);
            }
        }
        
    } catch (error) {
        console.error('Fetch error:', error);
        
        // More detailed error message
        let errorMessage = 'Nastala chyba při odesílání formuláře: ';
        
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            errorMessage += 'Nelze se připojit k serveru. Zkontrolujte, zda je PHP soubor send_email.php dostupný.';
        } else if (error.message.includes('HTTP error')) {
            errorMessage += `Server error (${error.message}). Zkontrolujte server logy.`;
        } else {
            errorMessage += error.message;
        }
        
        showNotification(errorMessage, 'error');
    } finally {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    // Set colors based on type
    let backgroundColor = '#3b82f6'; // default blue
    if (type === 'success') backgroundColor = '#10b981'; // green
    if (type === 'error') backgroundColor = '#ef4444'; // red
    if (type === 'warning') backgroundColor = '#f59e0b'; // orange
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: ${backgroundColor};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 1002;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds (longer for errors)
    const autoRemoveTime = type === 'error' ? 8000 : 5000;
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, autoRemoveTime);
}

// Add notification animations to CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
    
    .notification-close:hover {
        opacity: 0.8;
    }
`;
document.head.appendChild(style);

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const headerHeight = document.querySelector('.header').offsetHeight;
            const targetPosition = target.offsetTop - headerHeight - 20;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Seminar card quick actions
document.querySelectorAll('.seminar-card').forEach(card => {
    const reserveBtn = card.querySelector('.btn-primary');
    const moreInfoBtn = card.querySelector('.btn-outline');
    
    if (reserveBtn) {
        reserveBtn.addEventListener('click', () => {
            const seminarTitle = card.querySelector('h3').textContent;
            
            // Scroll to booking form
            const bookingSection = document.getElementById('booking');
            const headerHeight = document.querySelector('.header').offsetHeight;
            const targetPosition = bookingSection.offsetTop - headerHeight - 20;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
            
            // Pre-fill seminar selection
            setTimeout(() => {
                const seminarSelect = document.getElementById('seminar');
                const options = Array.from(seminarSelect.options);
                
                // Find matching option
                const matchingOption = options.find(option => 
                    option.textContent.toLowerCase().includes(seminarTitle.toLowerCase().substring(0, 10))
                );
                
                if (matchingOption) {
                    seminarSelect.value = matchingOption.value;
                }
            }, 500);
        });
    }
    
    if (moreInfoBtn) {
        moreInfoBtn.addEventListener('click', () => {
            const seminarTitle = card.querySelector('h3').textContent;
            const seminarDescription = card.querySelector('p').textContent;
            
            // Create modal for more info
            showSeminarModal(seminarTitle, seminarDescription);
        });
    }
});

// Seminar modal
function showSeminarModal(title, description) {
    // Remove existing modal
    const existingModal = document.querySelector('.seminar-modal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'seminar-modal';
    modal.innerHTML = `
        <div class="modal-backdrop" onclick="this.parentElement.remove()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>${title}</h2>
                <button class="modal-close" onclick="this.closest('.seminar-modal').remove()">×</button>
            </div>
            <div class="modal-body">
                <p>${description}</p>
                <p><strong>Formát:</strong> Uzavřený seminář ve vašem sídle</p>
                <p><strong>Cena:</strong> Na vyžádání podle počtu účastníků</p>
                <p><strong>Materiály:</strong> Všechny materiály jsou v ceně</p>
                <p><strong>Certifikát:</strong> Certifikát o absolvování</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="scrollToBooking(); this.closest('.seminar-modal').remove();">Rezervovat seminář</button>
                <button class="btn btn-outline" onclick="this.closest('.seminar-modal').remove();">Zavřít</button>
            </div>
        </div>
    `;
    
    // Add modal styles
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1003;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    `;
    
    document.body.appendChild(modal);
}

function scrollToBooking() {
    const bookingSection = document.getElementById('booking');
    const headerHeight = document.querySelector('.header').offsetHeight;
    const targetPosition = bookingSection.offsetTop - headerHeight - 20;
    
    window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
    });
}

// Add modal styles
const modalStyle = document.createElement('style');
modalStyle.textContent = `
    .modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }
    
    .modal-content {
        position: relative;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        color: #1f2937;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        line-height: 1;
    }
    
    .modal-close:hover {
        color: #374151;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-body p {
        margin-bottom: 1rem;
        color: #4b5563;
    }
    
    .modal-footer {
        display: flex;
        gap: 1rem;
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        justify-content: flex-end;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
`;
document.head.appendChild(modalStyle);

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    // Add loading animation to seminar cards
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out';
            }
        });
    });
    
    document.querySelectorAll('.seminar-card').forEach(card => {
        observer.observe(card);
    });
});

// Add scroll-to-top functionality
window.addEventListener('scroll', () => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > 500) {
        if (!document.querySelector('.scroll-to-top')) {
            const scrollBtn = document.createElement('button');
            scrollBtn.className = 'scroll-to-top';
            scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            scrollBtn.onclick = () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            };
            
            scrollBtn.style.cssText = `
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                width: 50px;
                height: 50px;
                background-color: #3b82f6;
                color: white;
                border: none;
                border-radius: 50%;
                font-size: 1.2rem;
                cursor: pointer;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                z-index: 1001;
                transition: all 0.3s ease;
                animation: slideUp 0.3s ease-out;
            `;
            
            document.body.appendChild(scrollBtn);
        }
    } else {
        const scrollBtn = document.querySelector('.scroll-to-top');
        if (scrollBtn) {
            scrollBtn.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                scrollBtn.remove();
            }, 300);
        }
    }
});

// Add scroll button animations
const scrollBtnStyle = document.createElement('style');
scrollBtnStyle.textContent = `
    .scroll-to-top:hover {
        background-color: #2563eb;
        transform: translateY(-2px);
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideDown {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(20px);
        }
    }
`;
document.head.appendChild(scrollBtnStyle);