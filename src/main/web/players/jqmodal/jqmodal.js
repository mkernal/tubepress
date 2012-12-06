/**
 * Copyright 2006 - 2012 Eric D. Hough (http://ehough.com)
 * 
 * This file is part of TubePress (http://tubepress.org) and is released 
 * under the General Public License (GPL) version 3
 *
 * Shrink your JS: http://developer.yahoo.com/yui/compressor/
 */
/*jslint browser: true, devel: true */
/*global jQuery TubePressEvents TubePressCss TubePressGlobalJsConfig */
var TubePressJqModalPlayer = (function () {

	'use strict';

	/* this stuff helps compression */
	var events	= TubePressEvents,
		name	= 'jqmodal',
		jquery	= jQuery,
		doc		= jquery(document),
		path	= TubePressGlobalJsConfig.baseUrl + '/src/main/web/players/jqmodal/lib/jqModal.',

		invoke = function (e, videoId, galleryId, width, height) {

			var element = jquery('<div id="jqmodal' + galleryId + videoId + '" style="visibility: none; height: ' + height + 'px; width: ' + width + 'px;"></div>').appendTo('body'),
				hider = function (hash) {
					hash.o.remove();
					hash.w.remove();
				};

			element.addClass('jqmWindow');
			element.jqm({ onHide : hider }).jqmShow();
		},

		populate = function (e, title, html, height, width, videoId, galleryId) {

			jquery('#jqmodal' + galleryId + videoId).html(html);
		};

	jquery.getScript(path + 'js', function () {}, true);
	TubePressCss.load(path + 'css');

	doc.bind(events.PLAYER_INVOKE + name, invoke);
	doc.bind(events.PLAYER_POPULATE + name, populate);
}());