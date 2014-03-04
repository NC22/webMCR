/*!
 * Bootstrap jquery-free v1.05 (c) NC22
 * This is fork of repository Bootstrap without jQuery v0.3.1
 * https://github.com/tagawa/bootstrap-without-jquery
 */

var setBootstrapEvents; // used for real-time events update

;(function() {
    'use strict';

    // querySelectorAll support for older IE
    // Source: http://weblogs.asp.net/bleroy/archive/2009/08/31/queryselectorall-on-old-ie-versions-something-that-doesn-t-work.aspx
    if (!document.querySelectorAll) {
        document.querySelectorAll = function(selector) {
            var style = document.styleSheets[0] || document.createStyleSheet();
            style.addRule(selector, "foo:bar");
            var all = document.all, resultSet = [];
            for (var i = 0, l = all.length; i < l; i++) {
                if (all[i].currentStyle.foo === "bar") {
                    resultSet[resultSet.length] = all[i];
                }
            }
            style.removeRule(0);
            return resultSet;
        };
    }

    // Get the "hidden" height of a collapsed element
    function getHiddenHeight(el) {
        var children = el.children;
        var height = 0;
        for (var i = 0, len = children.length, child; i < len; i++) {
            child = children[i];
            height += Math.max(child['clientHeight'], child['offsetHeight'], child['scrollHeight']);
        }
        return height;
    }

    // Show a dropdown menu
    function doDropdown(event) {
        event = event || window.event;
        var evTarget = event.currentTarget || event.srcElement;
        var target = evTarget.parentElement;
        var className = (' ' + target.className + ' ');
        
        if (className.indexOf(' ' + 'open' + ' ') > -1) {
            // Hide the menu
            className = className.replace(' open ', ' ');
            target.className = className;
        } else {
            // Show the menu
            target.className += ' open ';
        }
        return false;
    }
    
    function getElementByMix(str) {
    
        if (!str) return false;
        
        var type = '.';
        if (str.indexOf('#') > -1) var type = '#';
        
        var input = str.split(type)[1];
        if (!input) return false;
        
        var elements;
        
        if (type == '.') { 
        
            elements = document.getElementsByClassName(input);
            if (elements.length < 1) return false;
            else return elements[0];
            
        } else if (type == '#') return GetById(input);
        else return false;    
    }
    
    function toogleAccordion(event) {

        event = event || window.event;
        var target = event.currentTarget || event.srcElement;
        
        var element = getElementByMix(target.href);
        if (!element) {
            var element = getElementByMix(target.getAttribute('data-target')) // for single accordions prob.
            if (!element) return false;
        } 
        
        if (element.className.indexOf('in') > -1) {
            element.className = element.className.replace('in', '');
            element.setAttribute("style", "height: 0px");
        } else {
            element.className += ' in';
            element.setAttribute("style", "height: auto");
        }
        
        var accordionGroup = target.getAttribute('data-parent')        
        if (!accordionGroup) return false; // so, this is single accord
            
        accordionGroup = accordionGroup.split("#")[1];

    	var accordions = document.getElementsByClassName('accordion-group')

        for (var i=0; i<=accordions.length-1; ++i){ 
        
            var aBody = accordions[i].getElementsByClassName('accordion-body')
            var aGroup = accordions[i].getElementsByClassName('accordion-toggle')

            if (!aBody.length || aBody[0].id === element.id ) continue;
            if (!aGroup.length || aGroup[0].getAttribute('data-parent').split("#")[1] != accordionGroup ) continue;
            
            
            aBody[0].className = aBody[0].className.replace('in', '');
            aBody[0].setAttribute("style", "height: 0px");
        }	    
        return false;
    }
    
   function toogleTab(event) {

        event = event || window.event;
        var target = event.currentTarget || event.srcElement;
        var tabButton = target.parentElement
        var buttons = tabButton.parentElement.getElementsByTagName('li')
        var element = getElementByMix(target.href);
        
        if (tabButton.className.indexOf('active') > -1) {
            return false; 
        }
        
        if (element.className.indexOf('active') > -1) {
            element.className = element.className.replace('active', '');
        } else {
            tabButton.className += ' active';
            element.className += ' active';
        }
        
        for (i=0; i<=buttons.length-1; ++i){ 
            if (buttons[i] != tabButton) {
                buttons[i].className = buttons[i].className.replace('active', '');
            }
        } 
        
        var tabGroup = element.parentElement        
        if (!tabGroup) return false;

    	var divList   = document.getElementsByTagName('DIV')

        for (i=0; i<=divList.length-1; ++i){ 
            if (divList[i].className.indexOf('tab-pane')  > -1 && divList[i].id !== element.id) {
                divList[i].className = divList[i].className.replace('active', '');
            }
        }	    
        return false;
    }
    
    // Close a dropdown menu
    function closeDropdown(event) {

        event = event || window.event;
        var evTarget = event.currentTarget || event.srcElement;
        var target = evTarget.parentElement;
        
        setTimeout(function(){
            target.className = (' ' + target.className + ' ').replace(' open ', ' ')
        }, 300);

        return false;
    }

    // Close an alert box by removing it from the DOM
    function closeAlert(event) {
        event = event || window.event;
        var evTarget = event.currentTarget || event.srcElement;
        var alertBox = evTarget.parentElement;
        
        alertBox.parentElement.removeChild(alertBox);
        return false;
    }
    
    setBootstrapEvents = function() {
    
        // Set event listeners for dropdown menus
        var dropdowns = document.querySelectorAll('[data-toggle=dropdown]');
        for (var i = 0, dropdown, len = dropdowns.length; i < len; i++) {
            dropdown = dropdowns[i];
            dropdown.setAttribute('tabindex', '0'); // Fix to make onblur work in Chrome
            dropdown.onclick = doDropdown;
            dropdown.onblur = closeDropdown;
        }

        // Set event listeners for alert tabs
        var tabs = document.querySelectorAll('[data-toggle=tab]');
        for (var i = 0, len = tabs.length; i < len; i++) {
            tabs[i].onclick = toogleTab;
        }

        // Set event listeners for alert collapse
        var accords = document.querySelectorAll('[data-toggle=collapse]');
        for (var i = 0, len = accords.length; i < len; i++) {
            accords[i].onclick = toogleAccordion;
        }

        // Set event listeners for alert boxes
        var alerts = document.querySelectorAll('[data-dismiss=alert]');
        for (var i = 0, len = alerts.length; i < len; i++) {
            alerts[i].onclick = closeAlert;
        }
    }
    
    setBootstrapEvents();
})();