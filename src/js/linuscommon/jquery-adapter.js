/**
 * Compatibility adapter for deprecated/removed jQuery methods that are
 * required by third-party code.
 *
 * Internal LinusShops code should NEVER have a dependency on anything in here.
 * In an ideal world, we will eventually delete this code.
 *
 * If you depend on this, you will be subjected to the procession of shame.
 * http://imgur.com/0eOD4Lk
 *
 * @author Sam Schmidt <samuel@dersam.net>
 */

/**
 * Passthrough for $.load to on('load')
 * 
 * Used by: bazaarvoice
 * 
 * @deprecated
 * @param loadFunction Function to execute when this event fires.
 */
jQuery.fn.load = function(loadFunction) {
    this.on('load', loadFunction);
};
