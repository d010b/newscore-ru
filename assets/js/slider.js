/**
 * News Slider
 */
class NewsSlider {
    constructor(container) {
        this.container = container;
        this.slides = container.querySelectorAll('.slide');
        this.prevBtn = container.querySelector('.slider-prev');
        this.nextBtn = container.querySelector('.slider-next');
        this.dotsContainer = container.querySelector('.slider-dots');
        this.currentSlide = 0;
        this.slideInterval = null;
        this.autoplaySpeed = 5000;
        
        this.init();
    }
    
    init() {
        // Создаем точки навигации
        this.createDots();
        
        // Показываем первый слайд
        this.showSlide(0);
        
        // Автопрокрутка
        this.startAutoplay();
        
        // События кнопок
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prevSlide());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextSlide());
        }
        
        // События для точек
        this.dotsContainer.addEventListener('click', (e) => {
            const dot = e.target.closest('.slider-dot');
            if (dot) {
                const index = parseInt(dot.dataset.index);
                this.showSlide(index);
            }
        });
        
        // Пауза при наведении
        this.container.addEventListener('mouseenter', () => this.stopAutoplay());
        this.container.addEventListener('mouseleave', () => this.startAutoplay());
        
        // Свайпы для мобильных
        this.initTouch();
    }
    
    createDots() {
        this.dotsContainer.innerHTML = '';
        this.slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.className = 'slider-dot';
            dot.dataset.index = index;
            dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
            this.dotsContainer.appendChild(dot);
        });
    }
    
    showSlide(index) {
        // Скрываем все слайды
        this.slides.forEach(slide => {
            slide.classList.remove('active');
            slide.setAttribute('aria-hidden', 'true');
        });
        
        // Удаляем активный класс у всех точек
        this.dotsContainer.querySelectorAll('.slider-dot').forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Показываем текущий слайд
        this.currentSlide = index;
        this.slides[index].classList.add('active');
        this.slides[index].setAttribute('aria-hidden', 'false');
        
        // Активируем соответствующую точку
        this.dotsContainer.querySelectorAll('.slider-dot')[index].classList.add('active');
    }
    
    nextSlide() {
        let nextIndex = this.currentSlide + 1;
        if (nextIndex >= this.slides.length) {
            nextIndex = 0;
        }
        this.showSlide(nextIndex);
    }
    
    prevSlide() {
        let prevIndex = this.currentSlide - 1;
        if (prevIndex < 0) {
            prevIndex = this.slides.length - 1;
        }
        this.showSlide(prevIndex);
    }
    
    startAutoplay() {
        this.stopAutoplay();
        this.slideInterval = setInterval(() => this.nextSlide(), this.autoplaySpeed);
    }
    
    stopAutoplay() {
        if (this.slideInterval) {
            clearInterval(this.slideInterval);
            this.slideInterval = null;
        }
    }
    
    initTouch() {
        let startX = 0;
        let endX = 0;
        
        this.container.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });
        
        this.container.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            this.handleSwipe(startX, endX);
        });
    }
    
    handleSwipe(startX, endX) {
        const threshold = 50;
        const diff = startX - endX;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                this.nextSlide(); // Свайп влево
            } else {
                this.prevSlide(); // Свайп вправо
            }
        }
    }
}

// Инициализация слайдера при загрузке
document.addEventListener('DOMContentLoaded', function() {
    const sliderContainers = document.querySelectorAll('.news-slider');
    sliderContainers.forEach(container => {
        new NewsSlider(container);
    });
});