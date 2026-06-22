<?php  
include('includes/header.php'); 
?>

<style>
.banner{
    background:#3f3f3f;
    color:white;
    text-align:center;
    padding:40px 0;
    font-size:28px;
    font-weight:bold;
    letter-spacing:2px;
}

.about{
    padding:70px 0;
    background:#f6f6f6;
    border-bottom:1px solid #e5e5e5;
}

.about-grid{
    display:flex;
    gap:80px;
    flex-wrap:wrap;
}

.about-left,
.about-right{
    flex:1;
    min-width:280px;
    text-align:justify;
}

.about-left p{
    line-height:1.8;
    margin-bottom:15px;
}

.about-right img{
    width:100%;
    max-width:350px;       
    aspect-ratio:1/1;       
    object-fit:cover;       
    display:block;
    margin:0 auto;          
    border-radius:10px;
}

.why-us{
    padding:100px 0;
    background:#e5ddcf;
    text-align:center;
}

.why-title{
    font-size:36px;
    margin-bottom:20px;
    color:#000000;
}

.why-subtitle{
    margin-bottom:60px;
    font-size:15px;
    color:#000000;
}

.why-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:30px;
}

.why-box{
    background:white;
    padding:50px 30px;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.05);
    transition:0.35s;
    position:relative;
    overflow:hidden;
}

.why-box:hover{
    transform:translateY(-10px);
    box-shadow:0 20px 40px rgba(0,0,0,0.12);
}

.why-box::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:0%;
    height:4px;
    background:#b8860b;
    transition:0.3s;
}

.why-box:hover::before{
    width:100%;
}

.icon-circle{
    width:70px;
    height:70px;
    background:#f3f3f3;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 20px;
    transition:0.3s;
}

.why-box:hover .icon-circle{
    background:#b8860b;
}

.icon-circle img{
    width:32px;
    transition:0.3s;
}

.why-box:hover .icon-circle img{
    filter:brightness(0) invert(1);
}

.why-box h4{
    font-size:18px;
    color:#222;
    margin-bottom:12px;
    letter-spacing:1px;
}

.why-box p{
    font-size:14px;
    color:#666;
    line-height:1.7;
}

@media(max-width:900px){
    .why-grid{ grid-template-columns:1fr; }
}
</style>

<?php include('includes/chatbot.php'); ?>
<?php include('includes/slide.php'); ?>

<body>

<div class="banner">ABOUT US</div>

<div class="container about">
    <div class="about-grid">
        <div class="about-left">
            <p><b>Yong Sheng Alu Enterprise</b> specializes in the supply and installation of high-quality aluminium and glass products. We provide a wide range of services including aluminium doors, windows, cabinets, sliding doors, tempered glass, shower glass, and more. With a focus on quality workmanship and customer satisfaction, we ensure every project is completed with precision and care. Feel free to contact us for reliable and affordable solutions for your home or business.</p>
            <p>Based in Johor, Malaysia, we specialize in premium-quality glass doors and aluminium cabinets. With years of experience, we have built a strong reputation, with most of our projects coming from satisfied customer recommendations. Get in touch with us today for reliable and professional solutions.</p>
        </div>
        <div class="about-right">
            <img src="images/aboutus.jpg">
        </div>
    </div>
</div>

<section class="why-us">
    <div class="container">
        <h2 class="why-title">Why Choose Us</h2>
        <p class="why-subtitle">We deliver quality, trust and professional workmanship</p>
        <div class="why-grid">
            <div class="why-box">
                <div class="icon-circle"><img src="images/1.png"></div>
                <h4>Expertise & Experience</h4>
                <p>With years of experience in Johor, We delivers professional installation of high-quality aluminium and glass products, including doors, windows, cabinets, and shower glass.</p>
            </div>
            <div class="why-box">
                <div class="icon-circle"><img src="images/2.png"></div>
                <h4>Quality & Precision</h4>
                <p>We prioritize superior workmanship and attention to detail, ensuring every project is completed with precision and durability.</p>
            </div>
            <div class="why-box">
                <div class="icon-circle"><img src="images/3.png"></div>
                <h4>Trusted by Customers</h4>
                <p>Most of our projects come from satisfied customer recommendations, reflecting our commitment to reliability, affordability, and excellent service.</p>
            </div>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>

</body>
</html>