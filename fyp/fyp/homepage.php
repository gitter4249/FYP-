<?php 
include('includes/header.php'); 
?>

<style>
.profile-section {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    background: #fff;
    overflow: hidden;
    position: relative;
    border-bottom: 1px solid #eee;
}

.profile-left {
    flex: 0 0 50%;
    position: relative;
    overflow: hidden;
}

.profile-left img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

.about-watermark {
    position: absolute;
    right: -50px;
    top: 55%;
    transform: translateY(-50%) rotate(55deg);
    font-size: 70px;
    color: rgba(0,0,0,0.03);
    font-weight: bold;
    letter-spacing: 12px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 5;
}

.profile-right {
    flex: 0 0 50%;
    padding: 80px 100px 80px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #fff;
}

.profile-right h2 { font-size: 34px; color: #333; margin-bottom: 25px; font-weight: 400; letter-spacing: 1px; }
.profile-right h4 { font-size: 14px; color: #444; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; }
.profile-right p { font-size: 14px; line-height: 1.8; color: #666; text-align: justify; margin-bottom: 35px; }

.learn-more-btn {
    display: inline-block;
    background-color: #ffd453;
    color: #fff;
    padding: 13px 45px;
    text-decoration: none;
    font-size: 13px;
    font-weight: bold;
    width: fit-content;
    transition: 0.3s;
}

.learn-more-btn:hover {background: white; color: #20304a; border: 2px solid #ffd453;}

.why-choose-us {
    width: 100%;
    display: flex;
    min-height: 550px;
    overflow: hidden;
    position: relative;
    background-image: url('https://www.sternfenster.com/app/uploads/2016/11/aluminium-doors-uk.jpg');
    background-size: cover;
    background-position: left center;
    background-repeat: no-repeat;
    margin-bottom: 0 !important;
}

.why-choose-us::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.7) 35%, #fff 60%);
    z-index: 1;
}

.why-left {
    flex: 0 0 55%;
    padding: 80px 50px 80px 100px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
}

.why-left h2 { font-size: 32px; color: #333; letter-spacing: 2px; margin-bottom: 20px; text-transform: uppercase; }

.features-grid {
    display: flex;
    justify-content: space-around;
    gap: 40px;
    margin-bottom: 10px;
    width: 100%;
}

.feature-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: transform 0.3s ease;
}

.feature-item:hover { transform: translateY(-8px); }

.feature-item img {
    width: 70px;
    height: 70px;
    margin-bottom: 20px;
    object-fit: contain;
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
}

.feature-item p { font-size: 15px; color: #333; font-weight: bold; letter-spacing: 0.5px; }

.why-right {
    flex: 0 0 45%;
    position: relative;
    z-index: 3;
    background: #fff;
    clip-path: polygon(15% 0, 100% 0, 100% 100%, 0% 100%);
}

.why-right img { width: 100%; height: 100%; display: block; object-fit: cover; }

@media (max-width: 900px) {
    .profile-section, .why-choose-us { flex-direction: column; }
    .profile-left, .profile-right, .why-left, .why-right { flex: 0 0 100%; width: 100%; }
    .why-right { height: 350px; clip-path: none; }
    .features-grid { gap: 20px; }
}
</style>

<?php include('includes/chatbot.php'); ?>
<?php include('includes/slide.php'); ?>

<body>

<section class="profile-section">
    <div class="profile-left">
        <img src="images/home.png" alt="Profile">
        <div class="about-watermark">ABOUT US</div>
    </div>
    <div class="profile-right">
        <h2>COMPANY PROFILE</h2>
        <h4>YS ALUMINIUM SDN BHD WAS FOUNDED IN THE YEAR OF 2011.</h4>
        <p>Our core business starting from designing and installation for glass work, aluminium window & door, high performance multi-window door, aluminium sun louvre, partition, aluminium cabinet, wardrobe and shopfront.</p>
        <a href="aboutus.php" class="learn-more-btn">LEARN MORE</a>
    </div>
</section>

<section class="why-choose-us">
    <div class="why-left">
        <h2>Our Services</h2>
        <div class="features-grid">
            <div class="feature-item"><img src="images/5.png"><p>Design & Measurement</p></div>
            <div class="feature-item"><img src="images/6.png"><p>Installation Service</p></div>
            <div class="feature-item"><img src="images/4.png"><p>Glass & Repair</p></div>
        </div>
    </div>
    <div class="why-right">
        <img src="images/home2.png">
    </div>
</section>

<?php include('includes/footer.php'); ?>

</body>
</html>