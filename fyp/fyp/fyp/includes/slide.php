<style>
.slider{
    width:100%;
    height:600px; 
    position:relative;
    overflow:hidden;
    background:#0b0b0b; 
}

.slides{
    display:flex;
    width:100%;
    height:100%;
    transition:transform 0.6s ease;
}

.slide{
    width:100%;
    flex-shrink:0;
    display: flex;
    justify-content: center;
    align-items: center;
}

.slide img{
    height:100%; 
    object-fit:contain;
}

.dots{
    position:absolute;
    bottom:20px;
    left:50%;
    transform:translateX(-50%);
    display:flex;
    gap:10px;
}

.dot{
    width:12px;
    height:12px;
    border-radius:50%;
    background:white;
    opacity:0.6;
    cursor:pointer;
}

.dot.active{ opacity:1; background:white; }
</style>

<body>

<div class="slider">
    <div class="slides">
        <div class="slide"><img src="images/slide1.png"></div>
        <div class="slide"><img src="images/slide2.png"></div>
    </div>
    <div class="dots">
        <span class="dot active" onclick="currentSlide(0)"></span>
        <span class="dot" onclick="currentSlide(1)"></span>
    </div>
</div>


<script>
if (typeof slideIndex === 'undefined') {
    var slideIndex = 0; 
}

function showSlide(index){
    const slides = document.querySelector(".slides");
    const dots = document.querySelectorAll(".dot");
    if(!slides) return;
    
    slideIndex = index;
    slides.style.transform = "translateX(" + (-index * 100) + "%)";
    dots.forEach(dot => dot.classList.remove("active"));
    if(dots[index]) dots[index].classList.add("active");
}

function nextSlide(){
    slideIndex++;
    if(slideIndex > 1){ slideIndex = 0; }
    showSlide(slideIndex);
}

function currentSlide(index){
    showSlide(index);
}

setInterval(nextSlide, 4000);

</script>
</body>

