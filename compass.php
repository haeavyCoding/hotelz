<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Digital Compass Without Needle</title>
  <style>
    :root {
      --primary: #2563eb;
      --secondary: #dc2626;
      --text: #1e293b;
      --bg: #f8fafc;
      --card: #ffffff;
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background-color: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
      margin: 0;
      text-align: center;
    }
    .container {
      width: 100%;
      max-width: 320px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 15px;
    }
    header {
      margin-bottom: 10px;
    }
    h1 {
      color: var(--primary);
      font-size: 24px;
      margin-bottom: 5px;
    }
    .subtitle {
      color: #64748b;
      font-size: 14px;
    }
    .compass-container {
      width: 280px;
      height: 280px;
      position: relative;
      margin: 15px 0;
    }
    .compass-face {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background-color: var(--card);
      box-shadow: var(--shadow);
      border: 8px solid var(--primary);
      position: relative;
      overflow: hidden;
    }
    .compass-rose {
      width: 100%;
      height: 100%;
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="48" fill="none" stroke="%232563eb" stroke-width="1"/><path d="M50 5v10M95 50h-10M50 95v-10M5 50h10" stroke="%232563eb" stroke-width="1.5"/><path d="M70 15l-5 5M85 30l-5 5M85 70l-5-5M70 85l-5-5M30 85l5-5M15 70l5-5M15 30l5 5M30 15l5 5" stroke="%232563eb" stroke-width="1"/><text x="50" y="20" text-anchor="middle" font-size="10" fill="%23dc2626" font-weight="bold">N</text><text x="80" y="53" text-anchor="middle" font-size="9" fill="%232563eb">E</text><text x="50" y="85" text-anchor="middle" font-size="9" fill="%232563eb">S</text><text x="20" y="53" text-anchor="middle" font-size="9" fill="%232563eb">W</text><text x="70" y="30" text-anchor="middle" font-size="8" fill="%232563eb">NE</text><text x="70" y="75" text-anchor="middle" font-size="8" fill="%232563eb">SE</text><text x="30" y="75" text-anchor="middle" font-size="8" fill="%232563eb">SW</text><text x="30" y="30" text-anchor="middle" font-size="8" fill="%232563eb">NW</text></svg>');
      background-size: contain;
      position: absolute;
      top: 0;
      left: 0;
      transition: transform 0.2s ease-out;
    }
    .compass-center {
      position: absolute;
      width: 16px;
      height: 16px;
      background-color: var(--primary);
      border-radius: 50%;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 3;
    }
    .info-panel {
      background-color: var(--card);
      border-radius: 12px;
      padding: 15px;
      width: 100%;
      box-shadow: var(--shadow);
    }
    .direction {
      font-size: 22px;
      font-weight: 600;
      color: var(--primary);
      margin: 8px 0;
    }
    .details {
      display: flex;
      justify-content: space-between;
      margin-top: 10px;
    }
    .detail-box {
      flex: 1;
      padding: 8px;
    }
    .detail-value {
      font-size: 18px;
      font-weight: 600;
      color: var(--primary);
    }
    .detail-label {
      font-size: 12px;
      color: #64748b;
    }
    .message {
      padding: 12px;
      border-radius: 8px;
      margin-top: 15px;
      font-size: 14px;
      display: none;
    }
    .error {
      background-color: #fee2e2;
      color: #dc2626;
    }
    .instruction {
      background-color: #dbeafe;
      color: #1d4ed8;
    }
    .permission-btn {
      background-color: var(--primary);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 16px;
      margin-top: 15px;
      cursor: pointer;
      display: none;
    }
    @media (max-width: 400px) {
      .compass-container {
        width: 250px;
        height: 250px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Digital Compass</h1>
      <p class="subtitle">Find your direction easily</p>
    </header>

    <div class="compass-container">
      <div class="compass-face">
        <div class="compass-rose" id="compassRose"></div>
        <div class="compass-center"></div>
      </div>
    </div>

    <div class="info-panel">
      <div>You are facing:</div>
      <div class="direction" id="direction">--</div>
      <div class="details">
        <div class="detail-box">
          <div class="detail-value" id="degrees">0°</div>
          <div class="detail-label">Angle</div>
        </div>
        <div class="detail-box">
          <div class="detail-value" id="accuracy">--°</div>
          <div class="detail-label">Accuracy</div>
        </div>
      </div>
    </div>

    <div class="message instruction" id="instructions">
      <strong>Calibration needed:</strong> Move your device in a figure-8 motion
    </div>

    <button class="permission-btn" id="permissionBtn">Enable Compass</button>
    <div class="message error" id="error">
      Compass not available. Please use a mobile device with orientation sensors.
    </div>

    <div class="info-panel">
      <div style="margin-bottom: 10px;">
        <input type="date" id="dob" style="padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;" />
        <button onclick="calculateAge()" style="padding: 8px 12px; margin-left: 8px; border: none; border-radius: 6px; background-color: var(--primary); color: white;">Get Age</button>
      </div>
      <div class="detail-value" id="ageResult">Your age: --</div>
    </div>

    <div class="info-panel" id="weatherPanel">
      <div class="detail-value">Weather: <span id="weatherInfo">--</span></div>
    </div>
  </div>

  <script>
    const compassRose = document.getElementById('compassRose');
    const directionEl = document.getElementById('direction');
    const degreesEl = document.getElementById('degrees');
    const accuracyEl = document.getElementById('accuracy');
    const instructionsEl = document.getElementById('instructions');
    const errorEl = document.getElementById('error');
    const permissionBtn = document.getElementById('permissionBtn');

    const directions = ['North', 'Northeast', 'East', 'Southeast', 'South', 'Southwest', 'West', 'Northwest'];

    function getDirection(heading) {
      const index = Math.round(heading / 45) % 8;
      return directions[index];
    }

    function updateCompass(heading, accuracy) {
      compassRose.style.transform = `rotate(${heading}deg)`;
      degreesEl.textContent = `${Math.round(heading)}°`;
      directionEl.textContent = getDirection(heading);
      if (accuracy) accuracyEl.textContent = `±${Math.round(accuracy)}°`;
    }

    function initCompass() {
      if (typeof DeviceOrientationEvent.requestPermission === 'function') {
        permissionBtn.style.display = 'block';
        permissionBtn.addEventListener('click', requestPermission);
        return;
      }
      if (!window.DeviceOrientationEvent) {
        showError();
        return;
      }
      startCompass();
    }

    function requestPermission() {
      DeviceOrientationEvent.requestPermission()
        .then(response => {
          if (response === 'granted') {
            permissionBtn.style.display = 'none';
            startCompass();
          } else {
            showError('Permission denied. Please enable device orientation.');
          }
        })
        .catch(showError);
    }

    function startCompass() {
      errorEl.style.display = 'none';
      if ('webkitCompassHeading' in window) {
        window.addEventListener('deviceorientation', handleIOSCompass);
        return;
      }
      window.addEventListener('deviceorientation', handleStandardCompass);
      instructionsEl.style.display = 'block';
    }

    function handleIOSCompass(event) {
      if (event.webkitCompassHeading !== undefined) {
        updateCompass(event.webkitCompassHeading, event.webkitCompassAccuracy);
      }
    }

    function handleStandardCompass(event) {
      if (event.alpha !== null) {
        let heading = event.alpha;
        if (window.orientation) {
          if (Math.abs(window.orientation) === 90) {
            heading = (heading + (window.orientation > 0 ? 90 : -90)) % 360;
          }
        }
        if (heading < 0) heading += 360;
        updateCompass(heading);
      }
    }

    function showError(message) {
      errorEl.style.display = 'block';
      if (message) errorEl.textContent = message;
      if (window.innerWidth > 768) {
        let angle = 0;
        setInterval(() => {
          angle = (angle + 1) % 360;
          updateCompass(angle, 5);
        }, 50);
      }
    }

    function calculateAge() {
      const dobInput = document.getElementById("dob").value;
      const resultEl = document.getElementById("ageResult");

      if (!dobInput) {
        resultEl.textContent = "Please enter your birthdate.";
        return;
      }

      const dob = new Date(dobInput);
      const now = new Date();

      let ageYear = now.getFullYear() - dob.getFullYear();
      let ageMonth = now.getMonth() - dob.getMonth();
      let ageDay = now.getDate() - dob.getDate();

      if (ageDay < 0) {
        ageMonth--;
        ageDay += new Date(now.getFullYear(), now.getMonth(), 0).getDate();
      }

      if (ageMonth < 0) {
        ageYear--;
        ageMonth += 12;
      }

      const diff = now - dob;
      const seconds = Math.floor(diff / 1000) % 60;
      const minutes = Math.floor(diff / 60000) % 60;
      const hours = Math.floor(diff / 3600000) % 24;

      resultEl.textContent = `Your age: ${ageYear}y ${ageMonth}m ${ageDay}d ${hours}h ${minutes}m ${seconds}s`;
    }

    function fetchWeather() {
      if (!navigator.geolocation) {
        document.getElementById("weatherInfo").textContent = "Geolocation not supported.";
        return;
      }

      navigator.geolocation.getCurrentPosition(position => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        const apiKey = 'bd37352e57e5f7031530f1f2749a915d'; // Replace with your OpenWeatherMap API key

        fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&units=metric&appid=${apiKey}`)
          .then(res => res.json())
          .then(data => {
            const temp = data.main.temp;
            const condition = data.weather[0].description;
            const city = data.name;
            document.getElementById("weatherInfo").textContent = `${city}: ${temp}°C, ${condition}`;
          })
          .catch(err => {
            document.getElementById("weatherInfo").textContent = "Weather fetch failed.";
          });
      }, () => {
        document.getElementById("weatherInfo").textContent = "Location access denied.";
      });
    }

    window.addEventListener('load', () => {
      initCompass();
      fetchWeather();
    });
  </script>
</body>
</html>
