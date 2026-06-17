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

.contact-wrapper{
    background:#efefef;
    padding:70px 0;
}

.contact-row{
    display:flex;
    justify-content:space-between;
    gap:120px;
    flex-wrap:wrap;
}

.contact-form-left{
    flex:0 0 48%;
    min-width:300px;
}

.contact-form-left h2{
    margin-bottom:25px;
    color:#333;
}

.contact-form-left label{
    display:block;
    margin:12px 0 6px;
}

.contact-form-left input,
.contact-form-left textarea{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:5px;
    margin-bottom:15px;
}

.contact-form-left textarea{
    height:140px;
}

.contact-form-left button{
    background:#25D366;
    color:white;
    border:none;
    padding:14px;
    width:100%;
    border-radius:6px;
    cursor:pointer;
    font-weight: bold;
}

.contact-form-left button:hover{
    background:#1ebe5d;
}

.contact-info-right{
    flex:0 0 40%;
    min-width:280px;
}

.company-title{
    color:#2c3e50;
    margin-bottom:20px;
    font-size:20px;
}

.contact-info-right h4{
    color:#2c3e50;
    margin-top:25px;
    margin-bottom:10px;
}

.contact-info-right p{
    line-height:1.9;
}

.social-icons{
    display:flex;
    gap:15px;
    margin-top:15px;
}

.social-icons img{
    width:36px;
    height:36px;
    object-fit:contain;
    transition:0.3s;
}

.social-icons img:hover{
    transform:scale(1.2);
}

/* MAP */
.map-container iframe{
    width:100%;
    height:350px;
    border:0;
    display: block;
}
</style>

<?php include('includes/chatbot.php'); ?>
<?php include('includes/slide.php'); ?>

<body>

<div class="banner">CONTACT US</div>

<div class="contact-wrapper">
    <div class="container contact-row">
        
        <div class="contact-form-left">
            <h2>ENQUIRY</h2>
            <form id="waForm">
                <label>Contact Name:*</label>
                <input type="text" id="name">

                <label>Phone No</label>
                <input type="text" id="phone">

                <label>Email*</label>
                <input type="email" id="email">

                <label>Message*</label>
                <textarea id="message"></textarea>

                <button type="button" onclick="sendWhatsApp()">Send WhatsApp</button>
            </form>
        </div>

        <div class="contact-info-right">
            <h3 class="company-title">YS ALUMINIUM SDN BHD</h3>
            <p><b>Email:</b> yongshengalu@gmail.com</p>
            <p><b>Tel:</b> +60 18-366 5756</p>

            <h4>ADDRESS</h4>
            <p>
                46, Jalan Beladau 3<br>
                Taman Putri Wangsa<br>
                Ulu Tiram<br>
                Johor Bahru<br>
                Malaysia
            </p>

            <h4>FOLLOW US</h4>
            <div class="social-icons">
                <a href="https://www.facebook.com/profile.php?id=100054700453045"><img src="images/fb.png"></a>
                <a href="https://www.instagram.com/ys_aluminium_/"><img src="images/ins.png"></a>
                <a href="#"><img src="images/xhs.png"></a>
            </div>
        </div>

    </div>
</div>

<div class="map-container">
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.3361515228515!2d103.7946399!3d1.5937409!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da6dc2e9c15e8b%3A0xc6c7b98d3600f7c2!2s46%2C%20Jalan%20Beladau%203%2C%20Taman%20Putri%20Wangsa%2C%2081800%20Ulu%20Tiram%2C%20Johor!5e0!3m2!1sen!2smy!4v1710000000000" allowfullscreen="" loading="lazy"></iframe>
</div>

<?php include('includes/footer.php'); ?>

<script>
function sendWhatsApp(){
    let name = document.getElementById("name").value;
    let phone = document.getElementById("phone").value;
    let email = document.getElementById("email").value;
    let message = document.getElementById("message").value;

    if(!name || !email || !message){
        alert("Please fill in required fields!");
        return;
    }

    let text = `Name: ${name}%0APhone: ${phone}%0AEmail: ${email}%0AMessage: ${message}`;
    let url = `https://wa.me/60183665756?text=${text}`;
    window.open(url, '_blank');
}
</script>
</body>
</html>