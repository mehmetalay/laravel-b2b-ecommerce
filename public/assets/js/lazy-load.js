const images = document.querySelectorAll("[data-src]");

function preloadImage(img) {
    const src = img.getAttribute("data-src");
    if (!src) {
        return;
    }

    const spinner = document.createElement("div");
    spinner.classList.add("lazy-loading");

    img.parentNode.appendChild(spinner);

    img.src = src;
    img.onload = () => {
        spinner.remove();
        img.classList.remove("lazy-loading");
    };
}

const imgOptions = {};
const imgObserver = new IntersectionObserver((entries, imgObserver) => {
    entries.forEach(entry => {
        if (!entry.isIntersecting) {
            return;
        } else {
            preloadImage(entry.target);
            imgObserver.unobserve(entry.target);
        }
    });
}, imgOptions);

images.forEach(image => {
    imgObserver.observe(image);
});
