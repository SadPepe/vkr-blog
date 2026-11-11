// resources/js/Utilities/imageCarouselNode.js
import { Node } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";
import ImageCarouselNodeView from "@/Components/Editor/ImageCarouselNodeView.vue";

/**
 * Карусель фото в теле статьи.
 *
 * В content это <div class="image-carousel"> с несколькими <img>; санитайзер
 * такие теги и классы пропускает. На сайте обёртка превращается в scroll-snap
 * карусель с подписью «1 / N» и стрелками (Support/imageCarousel.js).
 *
 * В редакторе узел атомарный: кадры живут в атрибуте srcs, а NodeView рисует
 * горизонтальную ленту с плиткой «+» — добавлять и убирать фото можно на месте.
 */
const ImageCarousel = Node.create({
    name: "imageCarousel",
    group: "block",
    atom: true,
    selectable: true,
    draggable: true,

    addAttributes() {
        return {
            srcs: {
                default: [],
                parseHTML: (element) =>
                    [...element.querySelectorAll(":scope > img")]
                        .map((img) => img.getAttribute("src"))
                        .filter(Boolean),
                // Кадры отрисовываются в renderHTML, в атрибуты узла не попадают
                renderHTML: () => ({}),
            },
        };
    },

    parseHTML() {
        return [{ tag: "div.image-carousel" }];
    },

    renderHTML({ node }) {
        return [
            "div",
            { class: "image-carousel" },
            ...(node.attrs.srcs || []).map((src, index) => ["img", { src, alt: `Фото ${index + 1}` }]),
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(ImageCarouselNodeView);
    },
});

export default ImageCarousel;
