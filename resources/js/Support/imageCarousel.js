/**
 * Лента фото на странице: один горизонтальный ряд, кадры вплотную друг к другу.
 *
 * Разметку даёт редактор: <div class="image-carousel"><img>…</div>.
 * Модуль грузится лениво и только если на странице есть такая обёртка.
 * В редакторе та же разметка показывается плиткой с переносом — кадры видны все.
 */

const makeButton = (label, text) => {
    const button = document.createElement("button");
    button.type = "button";
    button.className = "image-carousel__nav-button";
    button.textContent = text;
    button.setAttribute("aria-label", label);

    return button;
};

export function enhanceImageCarousels(root) {
    if (!root) return 0;

    let enhanced = 0;

    root.querySelectorAll(".image-carousel").forEach((carousel) => {
        if (carousel.closest(".image-carousel-shell")) return;

        const images = [...carousel.querySelectorAll(":scope > img")];
        if (images.length < 2) return;

        const shell = document.createElement("div");
        shell.className = "image-carousel-shell";
        carousel.replaceWith(shell);

        const count = document.createElement("span");
        count.className = "image-carousel__counter";
        count.textContent = `${images.length} фото`;

        const prev = makeButton("Прокрутить ленту назад", "←");
        const next = makeButton("Прокрутить ленту вперёд", "→");
        const nav = document.createElement("div");
        nav.className = "image-carousel__nav";
        nav.append(prev, next);

        const bar = document.createElement("div");
        bar.className = "image-carousel__bar";
        bar.append(count, nav);

        // Шаг — 80% видимой ширины: кадры разной ширины, по одному их прокручивать неудобно
        const step = () => Math.max(carousel.clientWidth * 0.8, 240);
        const behavior = window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth";

        const render = () => {
            // Допуск 2px: при дробных ширинах кадров scrollLeft не доходит до max ровно
            prev.disabled = carousel.scrollLeft <= 2;
            next.disabled = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 2;
        };

        prev.addEventListener("click", () => carousel.scrollBy({ left: -step(), behavior }));
        next.addEventListener("click", () => carousel.scrollBy({ left: step(), behavior }));
        carousel.addEventListener("scroll", () => window.requestAnimationFrame(render), { passive: true });
        window.addEventListener("resize", render, { passive: true });

        carousel.tabIndex = 0;
        carousel.setAttribute("role", "group");
        carousel.setAttribute("aria-roledescription", "лента фотографий");
        carousel.addEventListener("keydown", (event) => {
            if (event.key !== "ArrowLeft" && event.key !== "ArrowRight") return;

            event.preventDefault();
            carousel.scrollBy({ left: event.key === "ArrowRight" ? step() : -step(), behavior });
        });

        shell.append(carousel, bar);
        render();
        enhanced += 1;
    });

    return enhanced;
}
