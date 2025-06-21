document.addEventListener('DOMContentLoaded', function() {
    const toTop = document.querySelector('.back-to-top');
    if (!toTop) return;
    window.addEventListener("scroll", function() {
        if(window.pageYOffset > 100) {
            toTop.classList.add('active-to-top');
        } else {
            toTop.classList.remove('active-to-top');
        }
    });
    toTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
});