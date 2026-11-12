import { Node, mergeAttributes } from "@tiptap/core";
import { VueNodeViewRenderer } from "@tiptap/vue-3";
import VideoEmbedNodeView from "@/Components/Editor/VideoEmbedNodeView.vue";

/**
 * Блок внешнего видео.
 *
 * В HTML сохраняется только data-обёртка без iframe. Плеер создаётся
 * VideoEmbedNodeView в редакторе и Support/videoEmbed.js на странице статьи.
 */
const VideoEmbed = Node.create({
    name: "videoEmbed",
    group: "block",
    atom: true,
    selectable: true,
    draggable: true,

    addAttributes() {
        return {
            provider: {
                default: "rutube",
                parseHTML: (element) => element.getAttribute("data-video-provider") || "rutube",
                renderHTML: (attributes) => ({ "data-video-provider": attributes.provider }),
            },
            videoId: {
                default: "",
                parseHTML: (element) => element.getAttribute("data-video-id") || "",
                renderHTML: (attributes) => ({ "data-video-id": attributes.videoId }),
            },
            hash: {
                default: "",
                parseHTML: (element) => element.getAttribute("data-video-hash") || "",
                renderHTML: (attributes) => ({ "data-video-hash": attributes.hash || "" }),
            },
        };
    },

    parseHTML() {
        return [{ tag: "div.video-embed[data-video-provider][data-video-id]" }];
    },

    renderHTML({ node, HTMLAttributes }) {
        return [
            "div",
            mergeAttributes(
                {
                    class: `video-embed video-embed--${node.attrs.provider || "unknown"}`,
                },
                HTMLAttributes,
            ),
        ];
    },

    addNodeView() {
        return VueNodeViewRenderer(VideoEmbedNodeView);
    },
});

export default VideoEmbed;
