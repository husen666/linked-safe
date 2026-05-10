import { escapeAttribute } from "@wordpress/escape-html";
const el = wp.element.createElement;
const icons = {};
icons.spspIcon = el('img', {src: escapeAttribute( smartPostShowGbScript.url + 'admin/GutenbergBlock/assets/smart-post-show-icon.svg' )})
export default icons;