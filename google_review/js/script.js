// document.addEventListener("DOMContentLoaded", function() {
//     fetchCounts();

//     function fetchCounts() {
//         fetch('server/fetchCounts.php')
//             .then(response => response.json())
//             .then(data => {
//                 document.getElementById('total-users').innerText = data.totalUsers;
//                 document.getElementById('google-review-users').innerText = data.googleReviewUsers;
//                 document.getElementById('custom-link-users').innerText = data.customLinkUsers;
//                 document.getElementById('inactive-users').innerText = data.inactiveUsers;
//             });
//     }
// });


document.addEventListener('DOMContentLoaded', function () {
    // Fetch data from the server
    fetch('server/getData.php')
        .then(response => response.json())
        .then(data => {
            // Update the UI with the fetched data
            document.getElementById('total-users').textContent = data.visit;
            document.getElementById('google-review-users').textContent = data.google;
            document.getElementById('custom-link-users').textContent = data.custom;
            document.getElementById('inactive-users').textContent = data.inactive;
        })
        .catch(error => console.error('Error fetching data:', error));
});
