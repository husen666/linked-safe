import icons from "./shortcode/blockIcon";
import DynamicShortcodeInput from "./shortcode/dynamicShortcode";
import { escapeAttribute, escapeHTML } from "@wordpress/escape-html";
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, PanelRow } from '@wordpress/components';
import { Fragment, createElement } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
const ServerSideRender = wp.serverSideRender;
const el = createElement;

/**
 * Register: Post Carousel Gutenberg Block.
 */
registerBlockType("smart-post-show-pro/shortcode", {
  title: escapeHTML( __("Smart Post Show", "post-carousel") ),
  description: escapeHTML( __(
    "Use Smart Post Show to insert a show in your page",
    "post-carousel"
  )),
  icon: icons.spspIcon,
  category: "common",
  supports: {
    html: true,
  },
  edit: (props) => {
    const { attributes, setAttributes } = props;
    const shortCodeList = smartPostShowGbScript.shortCodeList;
    
    let scriptLoad = ( shortcodeId ) => {
      let spspBlockLoaded = false;
      let spspBlockLoadedInterval = setInterval(function () {
        let uniqId = jQuery("#pcp_wrapper-" + shortcodeId).parents().attr('id');
        if (document.getElementById(uniqId)) {
          // Preloader JS
          jQuery("#pcp_wrapper-" + shortcodeId + " #pcp-preloader").css({ 'opacity': 0, 'display': 'none' }).animate({ opacity: 1 }, 600);
          //Actual functions goes here
          jQuery.getScript(smartPostShowGbScript.loadScript);
          spspBlockLoaded = true;
          uniqId = '';
        }
        if (spspBlockLoaded) {
          clearInterval(spspBlockLoadedInterval);
        }
        if ( 0 == shortcodeId ) {
          clearInterval(spspBlockLoadedInterval);
        }
      }, 10);
    }

    let updateShortcode = ( updateShortcode ) => {
      setAttributes({shortcode: escapeAttribute( updateShortcode.target.value )});
    }

    let shortcodeUpdate = (e) => {
      updateShortcode(e);
      let shortcodeId = escapeAttribute( e.target.value );
      scriptLoad(shortcodeId);	
    }

    if (jQuery('.sp-pcp-section:not(.sp-pcp-section-loaded)').length > 0 ) {
      let shortcodeId = escapeAttribute( attributes.shortcode );
      scriptLoad(shortcodeId);
    }

    if( attributes.preview ) {
      return (
        el('div', {className: 'spsp_shortcode_block_preview_image'},
          el('img', { src: escapeAttribute( smartPostShowGbScript.url + "admin/GutenbergBlock/assets/smart-post-show-block-preview.svg" )})
        )
      )
    }

    if ( shortCodeList.length === 0 ) {
      return (
        <Fragment>
          {
            el('div', {className: 'components-placeholder components-placeholder is-large'}, 
              el('div', {className: 'components-placeholder__label'}, 
                el('img', {className: 'block-editor-block-icon', src: escapeAttribute( smartPostShowGbScript.url + "admin/GutenbergBlock/assets/smart-post-show-icon.svg" )}),
                escapeHTML( __("Smart Post Show", "post-carousel") )
              ),
              el('div', {className: 'components-placeholder__instructions'}, 
                escapeHTML( __("No shortcode found. ", "post-carousel") ),
                el('a', {href: escapeAttribute( smartPostShowGbScript.link )}, 
                  escapeHTML( __("Create a shortcode now!", "post-carousel") )
                )
              )
            )
          }
        </Fragment>
      );
    }

    if ( ! attributes.shortcode || attributes.shortcode == 0 ) {
      return (
        <Fragment>
          <InspectorControls>
            <PanelBody title="Select a Show">
                <PanelRow>
                  <DynamicShortcodeInput
                    attributes={attributes}
                    shortCodeList={shortCodeList}
                    shortcodeUpdate={shortcodeUpdate}
                  />
                </PanelRow>
            </PanelBody>
          </InspectorControls>
          {
            el('div', {className: 'components-placeholder components-placeholder is-large'}, 
              el('div', {className: 'components-placeholder__label'},
                el('img', { className: 'block-editor-block-icon', src: escapeAttribute( smartPostShowGbScript.url + "admin/GutenbergBlock/assets/smart-post-show-icon.svg" )}),
                escapeHTML( __("Smart Post Show", "post-carousel") )
              ),
              el('div', {className: 'components-placeholder__instructions'}, escapeHTML( __("Select a Show", "post-carousel") ) ),
              <DynamicShortcodeInput
                attributes={attributes}
                shortCodeList={shortCodeList}
                shortcodeUpdate={shortcodeUpdate}
              />
            )
          }
        </Fragment>
      );
    }

    return (
      <Fragment>
        <InspectorControls>
            <PanelBody title="Select a Show">
                <PanelRow>
                  <DynamicShortcodeInput
                    attributes={attributes}
                    shortCodeList={shortCodeList}
                    shortcodeUpdate={shortcodeUpdate}
                  />
                </PanelRow>
            </PanelBody>
        </InspectorControls>
        <ServerSideRender block="smart-post-show-pro/shortcode" attributes={attributes} />
      </Fragment>
    );
  },
  save() {
    // Rendering in PHP
    return null;
  },
});
