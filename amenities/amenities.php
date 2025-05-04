
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Amenities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
        }

        .amenities-container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-size: 2.5em;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .amenity-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            text-align: center;
            cursor: pointer;
        }

        .amenity-card:hover {
            transform: translateY(-5px);
        }

        .amenity-icon {
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .amenity-title {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 10px;
        }

        .amenity-description {
            color: #666;
            font-size: 0.9em;
            line-height: 1.4;
        }

        /* Modal Styles */
        .amenity-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5em;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .amenities-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="amenities-container">
        <h2>Hotel Amenities</h2>
        <div class="amenities-grid" id="amenitiesGrid">
            <!-- Amenities will be populated by JavaScript -->
        </div>
    </div>

    <div class="amenity-modal" id="amenityModal">
        <div class="modal-content">
            <span class="close-modal" id="closeModal">&times;</span>
            <h3 class="modal-title" id="modalTitle"></h3>
            <p class="modal-description" id="modalDescription"></p>
        </div>
    </div>

    <script>
        // Amenity data
        const amenities = [
            {
                icon: 'fa-wifi',
                title: 'Free WiFi',
                description: 'High-speed internet access throughout the hotel'
            },
            {
                icon: 'fa-utensils',
                title: 'Restaurant',
                description: '24-hour dining with international cuisine'
            },
            {
                icon: 'fa-swimming-pool',
                title: 'Swimming Pool',
                description: 'Heated outdoor pool with lounge area'
            },
            {
                icon: 'fa-spa',
                title: 'Spa Services',
                description: 'Full-service spa with various treatments'
            },
            {
                icon: 'fa-dumbbell',
                title: 'Fitness Center',
                description: 'State-of-the-art gym equipment available 24/7'
            },
            {
                icon: 'fa-car',
                title: 'Parking',
                description: 'Complimentary valet parking service'
            }
        ];

        const grid = document.getElementById('amenitiesGrid');
        const modal = document.getElementById('amenityModal');
        const closeModal = document.getElementById('closeModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');

        // Generate amenity cards
        amenities.forEach(amenity => {
            const card = document.createElement('div');
            card.className = 'amenity-card';
            card.innerHTML = `
                <i class="fa-solid ${amenity.icon} amenity-icon"></i>
                <h3 class="amenity-title">${amenity.title}</h3>
                <p class="amenity-description">${amenity.description}</p>
            `;

            card.addEventListener('click', () => showModal(amenity));
            grid.appendChild(card);
        });

        // Show modal function
        function showModal(amenity) {
            modal.style.display = 'flex';
            modalTitle.textContent = amenity.title;
            modalDescription.textContent = amenity.description;
        }

        // Close modal
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>
