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
 * Note by Dane: remember that on top of the `load` method being used for
 * binding to events, there is also a shorthand `load` method for actually
 * loading content, similar to the way `get` is used. The `load` being used in
 * that way is being used by FeatherLight:362 to load ajax content directly
 * within a newly created node not yet insterted into the DOM. If moving to
 * jQuery3, this will need to be addressed.
 *
 * Read: http://api.jquery.com/load/
 *
 * @author Sam Schmidt <samuel@dersam.net>
 */

/**
 * Passthrough for $.load to on('load')
 *
 * Used by: bazaarvoice
 *
 * @deprecated
 * @shameful
 * @param loadFunction Function to execute when this event fires.
 */
// jQuery.fn.load = function(loadFunction) {
//     this.on('load', loadFunction);
// };
