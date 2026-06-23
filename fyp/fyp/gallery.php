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

.gallery{padding:80px 0;}

.gallery-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
}

.album-card {
    position: relative;
    height: 320px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.album-card img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition: transform 0.4s ease;
}

.album-card:hover img{
    transform: scale(1.06);
}

.album-overlay {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.65); 
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0; 
    transition: opacity 0.3s ease;
    padding: 20px;
    text-align: center;
    box-sizing: border-box;
}

.album-card:hover .album-overlay {
    opacity: 1;
}

.album-overlay h3 {
    margin: 0 0 10px 0;
    font-size: 20px;
    font-weight: 600;
    letter-spacing: 1px;
}

.album-overlay p {
    margin: 0;
    font-size: 14px;
    color: #e0e0e0;
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 20px;
}

#lightbox{
    display:none;
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,0.95);
    justify-content:center;
    align-items:center;
    z-index:3000;
}

#lightbox img{
    max-width:85%;
    max-height:80%;
    object-fit:contain;
    border:none;
    user-select: none;
}

#lightbox .close-btn{
    position:absolute;
    top:20px;
    right:30px;
    font-size:50px;
    color:white;
    cursor:pointer;
    transition: 0.2s;
}
#lightbox .close-btn:hover { color: #bbb; }

.lightbox-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 50px;
    color: white;
    cursor: pointer;
    user-select: none;
    padding: 15px;
    transition: 0.2s;
}
.lightbox-arrow:hover { color: #bbb; }
.prev-btn { left: 30px; }
.next-btn { right: 30px; }

#lightboxCounter {
    position: absolute;
    bottom: 30px;
    color: #888;
    font-size: 16px;
    letter-spacing: 1px;
}

@media(max-width:900px){
    .gallery-grid{grid-template-columns:repeat(2,1fr);}
    .lightbox-arrow { font-size: 40px; }
    .prev-btn { left: 10px; }
    .next-btn { right: 10px; }
}
@media(max-width:500px){
    .gallery-grid{grid-template-columns:1fr;}
    .album-card { height: 260px; }
}
</style>

<?php include('includes/chatbot.php'); ?>
<?php include('includes/slide.php'); ?>

<body>

<div class="banner">PROJECT GALLERY</div>

<div class="container gallery">
    <div class="gallery-grid">
        
        <div class="album-card" data-images="images/G6.jpg,images/G1.jpg,images/G2.jpg,images/G3.jpg" onclick="openAlbum(this)">
            <img src="images/G6.jpg" alt="Album Cover" loading="lazy"> 
            <div class="album-overlay">
                <h3>Reeded Glass Aluminium Swing Door</h3>
                <p>4 Photos</p>
            </div>
        </div>

        <div class="album-card" data-images="images/G0.jpg,images/G4.jpg,images/G5.jpg" onclick="openAlbum(this)">
            <img src="images/G0.jpg" alt="Album Cover" loading="lazy">
            <div class="album-overlay">
                <h3>Slim Frame Hanging Door</h3>
                <p>3 Photos</p>
            </div>
        </div>

        <div class="album-card" data-images="images/G7.jpg,images/G8.jpg,images/G9.jpg,images/G10.jpg" onclick="openAlbum(this)">
            <img src="images/G7.jpg" alt="Album Cover" loading="lazy">
            <div class="album-overlay">
                <h3>Swing Door</h3>
                <p>4 Photos</p>
            </div>
        </div>

    </div>
</div>

<div id="lightbox">
    <span class="close-btn" onclick="closeLightbox()">&times;</span>
    <span class="lightbox-arrow prev-btn" onclick="prevImage()">&#10094;</span>
    
    <img id="lightboxImg">
    
    <span class="lightbox-arrow next-btn" onclick="nextImage()">&#10095;</span>
    <div id="lightboxCounter"></div>
</div>

<?php include('includes/footer.php'); ?>

<script>
let currentAlbumImages = []; 
let currentImageIndex = 0;   

function openAlbum(albumElement) {
    const imagesStr = albumElement.getAttribute('data-images');
    if (!imagesStr) return;
    
    currentAlbumImages = imagesStr.split(',');
    currentImageIndex = 0; 
    updateLightbox();
    
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function updateLightbox() {
    if (currentAlbumImages.length === 0) return;
    
    document.getElementById('lightboxImg').src = currentAlbumImages[currentImageIndex];
    document.getElementById('lightboxCounter').innerText = `${currentImageIndex + 1} / ${currentAlbumImages.length}`;
}

function nextImage() {
    if (currentAlbumImages.length <= 1) return;
    currentImageIndex = (currentImageIndex + 1) % currentAlbumImages.length;
    updateLightbox();
}

function prevImage() {
    if (currentAlbumImages.length <= 1) return;
    currentImageIndex = (currentImageIndex - 1 + currentAlbumImages.length) % currentAlbumImages.length;
    updateLightbox();
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', function(e) {
    if (document.getElementById('lightbox').style.display === 'flex') {
        if (e.key === "ArrowRight") nextImage();
        if (e.key === "ArrowLeft") prevImage();
        if (e.key === "Escape") closeLightbox();
    }
});
</script>

</body>
</html>