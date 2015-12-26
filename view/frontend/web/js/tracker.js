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
         * Constructor
         *
         * @param Object options
         */
        'Henhed_Piwik/js/tracker': function(options) {
            this._injectScript(options.scriptUrl)
                .push(['setTrackerUrl', options.trackerUrl])
                .push(['setSiteId', options.siteId]);
            _.each(options.actions, this.push, this);
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
                _.each(cart.piwikActions, this.push, this);
            }
        },

        /**
         * Push an action to the tracker
         *
         * @param Array action
         * @returns Object
         */
        push: function (action) {
            exports._paq.push(action);
            return this;
        }
    };

    return tracker;
});
