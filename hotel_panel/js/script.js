document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    
    menuToggle.addEventListener('click', function() {
        wrapper.classList.toggle('toggled');
    });

    // Initialize charts
    initCharts();

    // Save hotel button event
    const saveHotelBtn = document.getElementById('saveHotelBtn');
    if (saveHotelBtn) {
        saveHotelBtn.addEventListener('click', function() {
            // Validate form
            const form = document.getElementById('hotelForm');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            // Here you would typically send data to server via AJAX
            alert('Hotel saved successfully!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('addHotelModal'));
            modal.hide();
            
            // Reset form
            form.reset();
            form.classList.remove('was-validated');
        });
    }

    // Simulate loading data
    setTimeout(() => {
        document.querySelectorAll('.card').forEach(card => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        });
    }, 300);
});

function initCharts() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue',
                    data: [5000, 6200, 7500, 8200, 9500, 10500, 12000, 11500, 11000, 12500, 14000, 15000],
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Booking Source Chart
    const bookingSourceCtx = document.getElementById('bookingSourceChart');
    if (bookingSourceCtx) {
        new Chart(bookingSourceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Direct', 'Website', 'Partners'],
                datasets: [{
                    data: [35, 45, 20],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                    hoverBorderColor: 'rgba(234, 236, 244, 1)',
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%',
            }
        });
    }
}

// Function to open add hotel modal
function openAddHotelModal() {
    const modal = new bootstrap.Modal(document.getElementById('addHotelModal'));
    modal.show();
}

// Add event listeners to sidebar items
document.querySelectorAll('.list-group-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.list-group-item').forEach(i => {
            i.classList.remove('active');
        });
        this.classList.add('active');
        
        // Here you would typically load content via AJAX based on which item was clicked
        const title = this.querySelector('i').nextSibling.textContent.trim();
        document.querySelector('.container-fluid h2').innerHTML = 
            `<i class="${this.querySelector('i').className} me-2"></i>${title}`;
    });
});