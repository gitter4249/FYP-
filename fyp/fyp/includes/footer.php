<style>
.site-footer {
    background-color: #20304a;
    color: #e2e8f0;
    padding: 60px 0 30px 0;
    clear: both;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-row {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr 1fr;
    gap: 50px;
    margin-bottom: 40px;
}

@media (max-width: 768px) {
    .footer-row {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}

.footer-col h4 {
    color: #ffffff;
    font-size: 15px;
    margin-bottom: 25px;
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    font-weight: bold;
}

.footer-col h4::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -10px;
    width: 35px;
    height: 2px;
    background: #ff2a2a;
}

.footer-col p {
    font-size: 13px;
    line-height: 1.8;
    margin-bottom: 12px;
    color: #cbd5e1;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 10px;
}

.footer-links a {
    text-decoration: none;
    color: #cbd5e1;
    font-size: 13px;
    transition: 0.3s;
}

.footer-links a:hover {
    color: #ffffff;
    padding-left: 5px;
}

.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.footer-bottom p {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}

.footer-socials {
    display: flex;
    gap: 12px;
}

.footer-socials a img {
    width: 34px;
    height: 34px;
    object-fit: contain;
    background: rgba(255, 255, 255, 0.1);
    padding: 7px;
    border-radius: 6px;
    transition: 0.3s ease;
}

.footer-socials a img:hover {
    background: #ff2a2a;
    transform: translateY(-3px);
}
</style>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-row">
            <div class="footer-col">
                <h4>YS ALUMINIUM SDN BHD</h4>
                <p>We are a dedicated company committed to providing high-quality aluminium solutions. With a focus on trust, transparency, and results.</p>
            </div>
            <div class="footer-col">
                <h4>QUICK LINKS</h4>
                <ul class="footer-links">
                    <li><a href="homepage.php">Home</a></li>
                    <li><a href="aboutus.php">About Us</a></li>
                    <li><a href="product.php">Products</a></li>
                    <li><a href="gallery.php">Gallery</a></li>
                    <li><a href="contactus.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>CONTACT INFORMATION</h4>
                <p><b>Phone:</b> +60 11-11960310</p>
                <p><b>Email:</b> yongshengalu@gmail.com</p>
                <p><b>Address:</b><br>
                46, Jalan Beladau 3, Taman Putri Wangsa,<br>
                Ulu Tiram, 81800 Johor Bahru, Malaysia</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2026 YS Aluminium Sdn Bhd. All Rights Reserved.</p>
            <div class="footer-socials">
                <a href="https://www.facebook.com/profile.php?id=100054700453045"><img src="images/fb.png" alt="Facebook"></a>
                <a href="https://www.instagram.com/ys_aluminium_/"><img src="images/ins.png" alt="Instagram"></a>
                <a href="https://www.xiaohongshu.com/user/profile/641fd5a00000000012011698?xsec_token=ABYAiRLm6o3Mdr-D0be8KFNvWu9JWvGKRJ2UGuOjmqI-w%3D&xsec_source=pc_search"><img src="images/xhs.png" alt="XHS"></a>
            </div>
        </div>
    </div>
</footer>

<script>
function toggleModal(show){
    const modal = document.getElementById('modalOverlay');
    if(modal) {
        modal.style.display = show ? 'block' : 'none';
        document.body.style.overflow = show ? 'hidden' : 'auto';
    }
}

window.addEventListener("scroll", function(){
    const navbar = document.querySelector(".navbar");
    if(navbar) {
        if(window.scrollY > 50) navbar.classList.add("shrink");
        else navbar.classList.remove("shrink");
    }
});

(function() {
    let currentIdx = 0;
    const slides = document.querySelector(".slides");
    const dots = document.querySelectorAll(".dot");

    if(slides && dots.length > 0) {
        window.showSlide = function(index) {
            currentIdx = index;
            slides.style.transform = `translateX(-${index * 100}%)`;
            dots.forEach(dot => dot.classList.remove("active"));
            dots[index].classList.add("active");
        };

        window.currentSlide = function(index) { showSlide(index); };

        setInterval(() => {
            currentIdx = (currentIdx + 1) % dots.length;
            showSlide(currentIdx);
        }, 4000);
    }
})();
</script>