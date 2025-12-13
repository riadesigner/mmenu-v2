export class ScrollManager {
    constructor(containerSelector, buttonSelector) {
        
        this.container = containerSelector[0]; // jqueryElement
        this.button = buttonSelector[0];       // jqueryElement 

        this.isAtBottom = false;
        
        if (!this.container || !this.button) {
            console.error('ScrollManager: Container or button not found');
            return;
        }
        
        this.init();
    }
    
    init() {
        console.log('init scrollers!')
        this.button.addEventListener('click', () => this.scrollToBottom());
        this.container.addEventListener('scroll', () => this.checkScrollPosition());
        this.setupResizeObserver();
        this.checkScrollPosition();
    }
    
    scrollToBottom() {        
        this.container.scrollTo({
            top: this.container.scrollHeight,
            behavior: 'smooth'
        });
    }
    
    checkScrollPosition() {
        const scrollTop = this.container.scrollTop;
        const scrollHeight = this.container.scrollHeight;
        const clientHeight = this.container.clientHeight;
        
        this.isAtBottom = Math.abs(scrollHeight - scrollTop - clientHeight) < 5;
        this.button.disabled = this.isAtBottom;
        this.updateButtonAppearance();
    }
    
    updateButtonAppearance() {
        if (this.button.disabled) {
            this.button.style.opacity = '0.5';
            // this.button.style.cursor = 'not-allowed';
        } else {
            this.button.style.opacity = '1';
            // this.button.style.cursor = 'pointer';
        }
    }
    
    setupResizeObserver() {
        if (typeof ResizeObserver !== 'undefined') {
            const resizeObserver = new ResizeObserver(() => {
                this.checkScrollPosition();
            });
            resizeObserver.observe(this.container);
        }
    }
    
    refresh() {
        this.checkScrollPosition();
    }
}

// Альтернативный экспорт функции
export function initScrollButton(containerId, buttonId) {
    return new ScrollManager(`#${containerId}`, `#${buttonId}`);
}

// Экспорт по умолчанию
export default ScrollManager;