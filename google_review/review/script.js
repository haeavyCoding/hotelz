// Array to store all selected files
let fileList = [];

// Function to handle star rating
function handleStarRating(star, category) {
    const stars = document.querySelectorAll(`.stars i[data-category="${category}"]`);
    const value = parseInt(star.dataset.value);
    stars.forEach((s, index) => {
        s.classList.toggle('active', index < value);
        s.textContent = index < value ? 'star' : 'star_border';
    });

    if (category === 'overall' && value >= 4) {
        // Redirect to Google review page for 4 or more stars
        window.location.href = 'https://search.google.com/local/writereview?placeid=ChIJafnn-PnimzkRe4fH-MCxq0M';
    } else {
        checkSubmitButton(); // Check if submit button can be enabled
    }
}

// Add event listeners to all star icons
document.querySelectorAll('.stars i').forEach((star) => {
    star.addEventListener('click', () => {
        handleStarRating(star, star.dataset.category);
    });
});

// Handle button toggle for single selection
function toggleButton(button, groupId) {
    const group = document.getElementById(groupId);
    const buttons = group.querySelectorAll('.button');
    buttons.forEach((btn) => {
        btn.classList.toggle('buttonClicked', btn === button);
    });
}

// Handle button toggle for multi-selection
function toggleMultiSelect(button) {
    button.classList.toggle('buttonClicked');
}

// Handle text area toggle
function toggleTextbox(button) {
    const container = document.getElementById('textAreaContainer');
    const buttonText = button.textContent;
    const textAreaId = `textArea-${buttonText.replace(/\s/g, '')}`;

    if (button.classList.toggle('buttonClicked')) {
        const textAreaDiv = document.createElement('div');
        textAreaDiv.id = textAreaId;
        textAreaDiv.innerHTML = `
            <label for="${textAreaId}">${buttonText}</label>
            <textarea id="${textAreaId}" placeholder="Write about ${buttonText}..."></textarea>
        `;
        container.appendChild(textAreaDiv);
    } else {
        const textAreaDiv = document.getElementById(textAreaId);
        if (textAreaDiv) container.removeChild(textAreaDiv);
    }
}

// Function to handle the Cancel button click
function cancelReview() {
    location.reload(); // Reload the page
}

// Function to check if the Submit button can be enabled
function checkSubmitButton() {
    const overallStars = document.querySelectorAll('#overall-stars .active').length;
    const submitButton = document.getElementById('submitReview');
    if (overallStars > 0) {
        submitButton.disabled = false; // Enable the submit button
    } else {
        submitButton.disabled = true; // Keep it disabled
    }
}

// Function to handle form submission
function submitReview() {
    const overallRating = document.querySelectorAll('#overall-stars .active').length;
    const roomsRating = document.querySelectorAll('#rooms-section .active').length;
    const serviceRating = document.querySelectorAll('#service-section .active').length;
    const locationRating = document.querySelectorAll('#location-section .active').length;
    const experience = document.getElementById('experience').value.trim();
    const tripType = document.querySelector('#trip-type .buttonClicked')?.textContent.trim();
    const travelWith = document.querySelector('#travel-with .buttonClicked')?.textContent.trim();

    // Collect selected hotel descriptions
    const descriptions = Array.from(document.querySelectorAll('.form-group .buttonClicked')).map(btn => btn.textContent);

    // Collect additional topics
    const topics = {};
    document.querySelectorAll('#textAreaContainer textarea').forEach(textarea => {
        topics[textarea.id] = textarea.value.trim();
    });

    // Create a FormData object to send data via POST
    const formData = new FormData();
    formData.append('overallRating', overallRating);
    formData.append('roomsRating', roomsRating);
    formData.append('serviceRating', serviceRating);
    formData.append('locationRating', locationRating);
    formData.append('experience', experience);
    formData.append('tripType', tripType);
    formData.append('travelWith', travelWith);
    formData.append('descriptions', JSON.stringify(descriptions));
    formData.append('topics', JSON.stringify(topics));

    // Add media files to the form data
    const mediaInput = document.getElementById('media-input');
    for (const file of fileList) {
        formData.append('media[]', file);
    }

    fetch('storeReview.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(data => {
            showThankYouPopup(); // Show the popup on success
        })
        .catch(error => console.error('Error:', error));
}

// Function to handle file input and preview
function handleFileInput(event) {
    const files = Array.from(event.target.files);
    const previewContainer = document.getElementById('mediaPreview');

    // Add new files to the fileList
    fileList = fileList.concat(files);

    // Clear previous previews
    previewContainer.innerHTML = '';

    // Generate previews for all files in fileList
    fileList.forEach(file => {
        const mediaElement = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
        const removeButton = document.createElement('span');

        // Create an object URL for the file and set it as the source
        mediaElement.src = URL.createObjectURL(file);
        mediaElement.className = 'preview-thumbnail';

        if (file.type.startsWith('video/')) {
            mediaElement.controls = true; // Show video controls for videos
        }

        removeButton.innerHTML = '&times;'; // Cross icon
        removeButton.className = 'remove-button';

        // Remove the media element and revoke the object URL when the remove button is clicked
        removeButton.addEventListener('click', () => {
            previewContainer.removeChild(mediaElement.parentElement);
            URL.revokeObjectURL(mediaElement.src); // Free memory

            // Remove the file from fileList
            fileList = fileList.filter(f => f !== file);
        });

        // Wrap the media element and remove button in a container
        const mediaContainer = document.createElement('div');
        mediaContainer.className = 'media-container';
        mediaContainer.appendChild(mediaElement);
        mediaContainer.appendChild(removeButton);

        // Append the container to the preview section
        previewContainer.appendChild(mediaContainer);
    });
}

// Event listener for the media input field
document.getElementById('media-input').addEventListener('change', handleFileInput);

// Function to show the thank you popup
function showThankYouPopup() {
    const popup = document.getElementById('thankYouPopup');
    popup.style.display = 'flex'; // Show the popup
}

// Event listener for the "OK" button to close the popup and refresh the page
document.getElementById('closePopupButton').addEventListener('click', function () {
    const popup = document.getElementById('thankYouPopup');
    popup.style.display = 'none'; // Hide the popup
    location.reload(); // Refresh the page after closing the popup
});
