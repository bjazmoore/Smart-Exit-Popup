jQuery(document).ready(function ($) {
    const DEBUG_MODE = false; // 👈 Toggle this to true when debugging

    if (DEBUG_MODE){
        if (typeof smartExitPopup !== 'undefined') {
            console.log("🧾 smartExitPopup settings received from PHP:", smartExitPopup);
        } else {
            console.warn("⚠️ smartExitPopup is not defined!");
        }
    }

    

    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        return; // Skip on mobile devices
    }

    let popupDisplayed = false;
    let lastX = null;
    let lastY = null;
    const buffer = 1;

    function triggerExitPopup(triggerType = 'unknown') {
        if (!popupDisplayed) {
            popupDisplayed = true;
            if (DEBUG_MODE) console.log(`🟡 Popup triggered via: ${triggerType}`);
            showExitPopup();
        }
    }

    function getContrastColor(hex) {
        if (!hex || hex === 'auto') return '#000000'; // fallback
        hex = hex.replace('#', '');
        if (hex.length === 3) {
            hex = hex.split('').map(c => c + c).join('');
        }
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        return brightness < 128 ? '#ffffff' : '#000000';
    }

    function hexToRGBA(hex, alpha) {
        hex = hex.replace('#', '');
        if (hex.length === 3) {
            hex = hex.split('').map(c => c + c).join('');
        }
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    

    $(document).on('mousemove', function (e) {
        if (popupDisplayed) return;

        if (lastX === null || lastY === null) {
            lastX = e.clientX;
            lastY = e.clientY;
            return;
        }

        const movingUp = e.clientY < lastY;
        const movingDown = e.clientY > lastY;
        const movingLeft = e.clientX < lastX;
        const movingRight = e.clientX > lastX;

        const atTop = e.clientY <= buffer && movingUp;
        const atLeft = e.clientX <= buffer && movingLeft;
        const atRight = e.clientX >= (window.innerWidth - buffer) && movingRight;
        const atBottom = e.clientY >= (window.innerHeight - buffer) && movingDown;

        if (DEBUG_MODE && atTop) console.log("⬆️ Mouse exit at top (mousemove)");
        if (DEBUG_MODE && atLeft) console.log("⬅️ Mouse exit at left (mousemove)");
        if (DEBUG_MODE && atRight) console.log("➡️ Mouse exit at right (mousemove)");
        if (DEBUG_MODE && atBottom) console.log("⬇️ Mouse exit at bottom (mousemove)");

        if (atTop || atLeft || atRight || atBottom) {
            triggerExitPopup('mousemove');
        }

        lastX = e.clientX;
        lastY = e.clientY;
    });

    document.addEventListener('mouseout', function (e) {
        const leavingWindow = !e.toElement && !e.relatedTarget;

        if (leavingWindow) {
            const fromTop = e.clientY <= 10;
            const fromLeft = e.clientX <= 10;
            const fromRight = e.clientX >= window.innerWidth - 10;
            const fromBottom = e.clientY >= window.innerHeight - 10;

            if (fromTop || fromLeft || fromRight || fromBottom) {
                if (DEBUG_MODE) {
                    const dir = fromTop ? 'top' : fromLeft ? 'left' : fromRight ? 'right' : 'bottom';
                    console.log(`🚪 Mouse left viewport at ${dir} (mouseout)`);
                }
                triggerExitPopup('mouseout');
            } else if (DEBUG_MODE) {
                console.log("ℹ️ mouseout detected outside viewport, but not near edge");
                console.log("→ clientX:", e.clientX, "| clientY:", e.clientY);
            }
        }
    });

    function showExitPopup() {
        const scale = smartExitPopup.imageScale;

        let leftCSS;
        switch (smartExitPopup.imageXPosition) {
            case 'left5': leftCSS = '5%'; break;
            case 'left25': leftCSS = '25%'; break;
            case 'right5': leftCSS = '95%'; break;
            case 'right25': leftCSS = '75%'; break;
            case 'custom': leftCSS = `${smartExitPopup.imageXCustom}rem`; break;
            case 'center':
            default: leftCSS = '50%';
        }

        const translate = smartExitPopup.imageXPosition === 'center' ? 'translateX(-50%)' : '';

        const imageHtml = (smartExitPopup.showImage && smartExitPopup.imageUrl)
            ? `<img class="exit-popup-image" src="${smartExitPopup.imageUrl}" style="
                top:${smartExitPopup.imageY}px;
                left:${leftCSS};
                transform: ${translate} scale(${scale});
            ">`
            : '';

        let headerBg = '';
        if (smartExitPopup.headerColorMode === 'gradient') {
            headerBg = `linear-gradient(to right, ${smartExitPopup.headerColorLeft}, ${smartExitPopup.headerColorRight})`;
        } else {
            headerBg = smartExitPopup.headerColorLeft;
        }

        const resolvedHeaderTextColor = (smartExitPopup.headerTextColorMode === 'custom')
            ? smartExitPopup.headerTextColor
            : getContrastColor(smartExitPopup.headerColorLeft);
        
        const resolvedFooterTextColor = (smartExitPopup.footerTextColorMode === 'custom')
            ? smartExitPopup.footerTextColor
            : getContrastColor(smartExitPopup.footerBgColor);
    
        const headerHtml = smartExitPopup.headerEnabled
        ? `<div class="exit-popup-header"
                    style="height:${smartExitPopup.headerHeight}rem;
                        background:${headerBg};
                        border-top-left-radius:${smartExitPopup.modalRadius}px;
                        border-top-right-radius:${smartExitPopup.modalRadius}px;">
                <h2 class="exit-popup-header-text"
                    style="font-size:${smartExitPopup.headerFontSize}rem; color: ${resolvedHeaderTextColor};">${smartExitPopup.headerText}</h2>
            </div>`
        : '';       
        
        let footerHtml = '';
        if (smartExitPopup.showFooter) {
            footerHtml = `
                <div class="exit-popup-footer-bar" style="background: ${smartExitPopup.footerBgColor}; color: ${resolvedFooterTextColor};">
                    ${smartExitPopup.enableDismissOption ? `
                        <label class="exit-popup-dismiss-label" style="color: ${resolvedFooterTextColor};">
                            <input type="checkbox" id="exit-popup-dismiss" />
                            Don't show this again
                        </label>
                    ` : ''}
                </div>`;
        }

        const dismissed = localStorage.getItem('smartExitPopupDismissed') === 'true';
        if (DEBUG_MODE) console.log("🧠 Checking dismissal preference:", dismissed);

        if (dismissed) {
            if (DEBUG_MODE) console.log("✅ Popup dismissed previously, skipping.");
            return;
        }

        const totalHeight =
            smartExitPopup.modalHeightMode === 'custom' && smartExitPopup.modalHeightCustom > 0
                ? parseFloat(smartExitPopup.modalHeightCustom || 0) +
                parseFloat(smartExitPopup.headerHeight || 0) +
                parseFloat(smartExitPopup.footerHeightRem || 3)
                : null;

        const overlayColor = smartExitPopup.overlayBgColor || '#000000';
        const overlayAlpha = parseFloat(smartExitPopup.overlayAlpha || 0.7);
        const overlayRGBA = hexToRGBA(overlayColor, overlayAlpha);

        const popup = $(`
            <div class="exit-popup-overlay" style="background-color: ${overlayRGBA};">
                <div class="exit-popup-wrapper">
                    ${imageHtml}
                    <div class="exit-popup"
                        style="
                            background:${smartExitPopup.modalBg};
                            border-radius:${smartExitPopup.modalRadius}px;
                            width:${smartExitPopup.modalWidth}vw;
                            ${totalHeight ? `height:${totalHeight}rem;` : ''}
                        ">
                        ${headerHtml}
                        <button class="exit-close" aria-label="Close popup">
                            <img src="${smartExitPopup.pluginUrl}/assets/x-circle.svg" alt="Close" />
                        </button>
                        <div class="popup-content">
                            ${smartExitPopup.content}
                        </div>
                        ${footerHtml}
                    </div>
                </div>
            </div>
        `);
        
        // if (DEBUG_MODE) {
        //     console.log("👀 Popup HTML being inserted:");
        //     console.log(popup[0].outerHTML);
        // } 


        $('body').append(popup.hide());
        popup.fadeIn(300);

        $('.exit-close, .exit-popup-overlay').click(function () {
            const dismissChecked = $('#exit-popup-dismiss').is(':checked');
            if (DEBUG_MODE) console.log("📦 Popup closed. 'Don't show again' checked?", dismissChecked);

            if (dismissChecked) {
                localStorage.setItem('smartExitPopupDismissed', 'true');
                if (DEBUG_MODE) console.log("💾 Dismissal preference saved to localStorage.");
            }

            $('.exit-popup-overlay').fadeOut(200, function () {
                $(this).remove();
            });
        });

        $('.exit-popup').click(function (e) {
            e.stopPropagation();
        });
    }
});
