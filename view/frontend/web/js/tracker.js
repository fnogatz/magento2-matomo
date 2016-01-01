/**
 * Copyright 2015 Henrik Hedelund
 *
 * This file is part of Henhed_Piwik.
 *
 * Henhed_Piwik is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Henhed_Piwik is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Henhed_Piwik.  If not, see <http://www.gnu.org/licenses/>.
 */

define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data'
], function ($, _, customerData) {
    'use strict';

    var exports = window;

    exports._paq = exports._paq || [];

    var tracker = {

        /**
         * Piwik singleton/namespace
         *
         * @var Object
         */
        _piwik: null,

        /**
         * Constructor
         *
         * @param Object options
         */
        'Henhed_Piwik/js/tracker': function(options) {

            // Store options as config
            this._config = options;

            // Initialize Piwik singleton
            exports.piwikAsyncInit = (function () {
                this._piwik = exports.Piwik;
            }).bind(this);

            // Inject Piwik script and configure async tracker
            this._injectScript(options.scriptUrl)
                .push(['setTrackerUrl', options.trackerUrl])
                .push(['setSiteId', options.siteId]);

            // Push given actions to async tracker
            _.each(options.actions, function (action) {
                this.push(action);
            }, this);

            // Subscribe to cart updates
            customerData.get('cart').subscribe(this._cartUpdated, this);
        },

        /**
         * Append Piwik tracker script url to head
         *
         * @param String scriptUrl
         * @returns Object
         */
        _injectScript: function (scriptUrl) {
            $('<script>')
                .attr('type', 'text/javascript')
                .attr('async', true)
                .attr('defer', true)
                .attr('src', scriptUrl)
                .appendTo('head');
            return this;
        },

        /**
         * Callback for cart customer data subscriber
         *
         * @param Object cart
         * @see \Henhed\Piwik\CustomerData\Checkout\CartPlugin
         */
        _cartUpdated: function (cart) {
            if (_.has(cart, 'piwikActions')) {
                // We need to create a new tracker instance for asynchronous
                // ecommerce updates since previous ecommerce items are stored
                // in the tracker.
                this.createTracker().then((function (tracker) {
                    _.each(cart.piwikActions, function (action) {
                        this.push(action, tracker);
                    }, this);
                }).bind(this));
            }
        },

        /**
         * Get Piwik singleton/namespace promise
         *
         * @returns Promise
         */
        getPiwik: function () {
            var deferred = $.Deferred();
            if (this._piwik === null) {
                var intervalId = window.setInterval((function () {
                    if (this._piwik !== null) {
                        window.clearInterval(intervalId);
                        deferred.resolve(this._piwik);
                    }
                }).bind(this), 100);
            } else {
                deferred.resolve(this._piwik);
            }
            return deferred.promise();
        },

        /**
         * Create a new Piwik tracker returned as a promise
         *
         * @param String|undefined trackerUrl
         * @param Number|undefined siteId
         * @returns Promise
         */
        createTracker: function (trackerUrl, siteId) {
            var deferred = $.Deferred();
            this.getPiwik().then((function (piwik) {
                deferred.resolve(piwik.getTracker(
                    trackerUrl || this._config.trackerUrl,
                    siteId || this._config.siteId
                ));
            }).bind(this));
            return deferred.promise();
        },

        /**
         * Push an action to the given tracker. If the tracker argument is
         * omitted the action will be picked up by the async tracker.
         *
         * @param Array action
         * @param Tracker|undefined tracker
         * @returns Object
         */
        push: function (action, tracker) {
            if (typeof tracker === 'object') {
                var actionName = action.shift();
                if (typeof tracker[actionName] === 'function') {
                    tracker[actionName].apply(tracker, action);
                } else {
                    console.error('Undefined tracker function: ' + actionName);
                }
            } else {
                exports._paq.push(action);
            }
            return this;
        }
    };

    return tracker;
});
