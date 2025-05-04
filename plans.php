<?php 
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: auth_system/login.php');
    exit();
}
include_once('layouts/header.php');
?>
  <meta charset="UTF-8">
  <title>Hotel Plans</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    :root {
      --primary-color: #3a86ff;
      --secondary-color: #8338ec;
      --accent-color: #ff006e;
      --light-color: #f8f9fa;
      --dark-color: #212529;
      --text-color: #495057;
      --border-radius: 12px;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f5f7fa;
      color: var(--text-color);
      line-height: 1.6;
    }

    /* .app-main {
      width: 100%;
      min-height: 100vh;
      padding: 80px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    } */

    .app-main h1 {
      font-size: 2.5rem;
      color: var(--dark-color);
      margin-bottom: 2rem;
      text-align: center;
      font-weight: 600;
      position: relative;
      padding-bottom: 15px;
    }

    .app-main h1::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      border-radius: 2px;
    }

    .plans-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      /* max-width: 1440px; */
      width: 100%;
    }

    .plan-card {
      background: #fff;
      border-radius: var(--border-radius);
      width: 100%;
      max-width: 320px;
      padding: 40px 30px;
      text-align: center;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .plan-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }

    .plan-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
    }

    .plan-icon {
      font-size: 3rem;
      margin-bottom: 1.5rem;
      color: var(--primary-color);
      transition: var(--transition);
    }

    .plan-card:hover .plan-icon {
      transform: scale(1.1);
    }

    .plan-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
    }

    .plan-price {
      font-size: 1.75rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: var(--secondary-color);
    }

    .plan-price span {
      font-size: 1rem;
      font-weight: 400;
      color: var(--text-color);
    }

    .plan-features {
      list-style: none;
      padding: 0;
      margin-bottom: 2.5rem;
      text-align: left;
    }

    .plan-features li {
      margin: 12px 0;
      color: var(--text-color);
      font-size: 0.95rem;
      position: relative;
      padding-left: 25px;
    }

    .plan-features li::before {
      content: '\f00c';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      position: absolute;
      left: 0;
      color: var(--primary-color);
    }

    .plan-button {
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 500;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      transition: var(--transition);
      width: 100%;
      max-width: 200px;
      box-shadow: 0 4px 15px rgba(58, 134, 255, 0.3);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .plan-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(58, 134, 255, 0.4);
    }

    .plan-button:active {
      transform: translateY(0);
    }

    /* Popular Plan Styling */
    .plan-card.popular {
      border: 2px solid var(--primary-color);
    }

    .popular-badge {
      position: absolute;
      top: 20px;
      right: -30px;
      background: var(--accent-color);
      color: white;
      padding: 5px 30px;
      font-size: 0.8rem;
      font-weight: 600;
      transform: rotate(45deg);
      box-shadow: 0 2px 10px rgba(255, 0, 110, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .plans-container {
        gap: 20px;
      }
      
      .plan-card {
        max-width: 280px;
        padding: 30px 20px;
      }
    }

    @media (max-width: 768px) {
      .app-main {
        padding: 60px 15px;
      }
      
      .app-main h1 {
        font-size: 2rem;
      }
      
      .plans-container {
        flex-direction: column;
        align-items: center;
        gap: 25px;
      }
      
      .plan-card {
        max-width: 100%;
        width: 100%;
      }
    }

    @media (max-width: 576px) {
      .app-main h1 {
        font-size: 1.75rem;
      }
      * {
      font-size: 14px;
    }

      
      .plan-button {
        padding: 10px 20px;
        font-size: 0.9rem;
      }
    }
  </style>

  
<main class="app-main">
  <h1>Choose Your Perfect Plan</h1>
  <div class="plans-container">

    <div class="plan-card">
      <div class="plan-icon"><i class="fas fa-bed"></i></div>
      <div class="plan-title">Basic</div>
      <div class="plan-price">Free <span>/ forever</span></div>
      <ul class="plan-features">
        <li>Up to 6 property listings</li>
        <li>Basic property details</li>
        <li>Standard visibility</li>
        <li>Email support</li>
        <li>Monthly analytics report</li>
      </ul>
      <button class="plan-button" onclick="location.href='add_hotels.php?plan=1'">Get Started</button>
    </div>

    <div class="plan-card popular">
      <div class="popular-badge">Popular</div>
      <div class="plan-icon"><i class="fas fa-concierge-bell"></i></div>
      <div class="plan-title">Advance</div>
      <div class="plan-price">₹5,000 <span>/ month</span></div>
      <ul class="plan-features">
        <li>Up to 10 property listings</li>
        <li>Advanced property details</li>
        <li>Priority visibility</li>
        <li>24/7 phone support</li>
        <li>Weekly analytics report</li>
        <li>Promotional offers</li>
      </ul>
      <button class="plan-button" onclick="location.href='add_hotels.php?plan=2'">Choose Plan</button>
    </div>

    <div class="plan-card">
      <div class="plan-icon"><i class="fas fa-crown"></i></div>
      <div class="plan-title">Premium</div>
      <div class="plan-price">₹10,000 <span>/ month</span></div>
      <ul class="plan-features">
        <li>Unlimited property listings</li>
        <li>Premium property showcase</li>
        <li>Top visibility in search</li>
        <li>Dedicated account manager</li>
        <li>Real-time analytics dashboard</li>
        <li>Advanced booking system</li>
        <li>Marketing campaigns</li>
      </ul>
      <button class="plan-button" onclick="location.href='add_hotels.php?plan=3'">Premium Access</button>
    </div>

  </div>
</main>

<?php include_once('layouts/footer.php'); ?>