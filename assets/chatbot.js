/**
 * est Financial Chatbot WordPress Plugin JavaScript
 */

(function($) {
    'use strict';

    class EstChatbotWidget {
        constructor() {
            this.isOpen = false;
            this.session = {};
            this.isTyping = false;
            this.container = null;
            this.widget = null;
            this.messagesContainer = null;
            this.input = null;
            this.sendButton = null;
            
            this.init();
        }
        
        init() {
            // Wait for DOM to be ready
            $(document).ready(() => {
                this.createWidget();
                this.bindEvents();
                this.setThemeColor();
                this.setPosition();
            });
        }
        
        createWidget() {
            const container = $('#est-chatbot-container, #est-chatbot-inline');
            if (container.length === 0) return;
            
            this.container = container;
            const isInline = container.attr('id') === 'est-chatbot-inline';
            
            if (isInline) {
                this.createInlineWidget();
            } else {
                this.createFloatingWidget();
            }
        }
        
        createFloatingWidget() {
            const html = `
                <button class="est-chatbot-toggle" aria-label="Open Chat">
                    💬
                </button>
                <div class="est-chatbot-widget" style="display: none;">
                    <div class="est-chatbot-header">
                        <h3>est Financial</h3>
                        <button class="close-btn" aria-label="Close Chat">×</button>
                    </div>
                    <div class="est-chatbot-messages">
                        <div class="est-chatbot-welcome">
                            👋 Welcome! I'm here to help you with est Financial services.
                        </div>
                    </div>
                    <div class="est-chatbot-input-container">
                        <input type="text" class="est-chatbot-input" placeholder="Type your message..." maxlength="500">
                        <button class="est-chatbot-send" aria-label="Send Message">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22,2 15,22 11,13 2,9"></polygon>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            this.container.html(html);
            this.widget = this.container.find('.est-chatbot-widget');
            this.messagesContainer = this.container.find('.est-chatbot-messages');
            this.input = this.container.find('.est-chatbot-input');
            this.sendButton = this.container.find('.est-chatbot-send');
        }
        
        createInlineWidget() {
            const html = `
                <div class="est-chatbot-widget" style="display: block;">
                    <div class="est-chatbot-header">
                        <h3>est Financial AI Assistant</h3>
                    </div>
                    <div class="est-chatbot-messages">
                        <div class="est-chatbot-welcome">
                            👋 Welcome! I'm here to help you with est Financial services.
                        </div>
                    </div>
                    <div class="est-chatbot-input-container">
                        <input type="text" class="est-chatbot-input" placeholder="Type your message..." maxlength="500">
                        <button class="est-chatbot-send" aria-label="Send Message">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22,2 15,22 11,13 2,9"></polygon>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
            
            this.container.html(html);
            this.container.addClass('est-chatbot-inline');
            this.widget = this.container.find('.est-chatbot-widget');
            this.messagesContainer = this.container.find('.est-chatbot-messages');
            this.input = this.container.find('.est-chatbot-input');
            this.sendButton = this.container.find('.est-chatbot-send');
            this.isOpen = true;
            
            // Send initial greeting for inline widget
            setTimeout(() => {
                this.addBotMessage("Hello! I'm here to help you with est Financial services. I can provide information about our services or help you book an appointment. What would you like to do?", ["Learn about services", "Book an appointment"]);
            }, 1000);
        }
        
        bindEvents() {
            // Toggle button
            this.container.on('click', '.est-chatbot-toggle', (e) => {
                e.preventDefault();
                this.toggleWidget();
            });
            
            // Close button
            this.container.on('click', '.close-btn', (e) => {
                e.preventDefault();
                this.closeWidget();
            });
            
            // Send button
            this.container.on('click', '.est-chatbot-send', (e) => {
                e.preventDefault();
                this.sendMessage();
            });
            
            // Enter key
            this.container.on('keypress', '.est-chatbot-input', (e) => {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            // Option buttons
            this.container.on('click', '.est-chatbot-option', (e) => {
                e.preventDefault();
                const option = $(e.target).text();
                this.selectOption(option);
            });
        }
        
        toggleWidget() {
            if (this.isOpen) {
                this.closeWidget();
            } else {
                this.openWidget();
            }
        }
        
        openWidget() {
            this.widget.show();
            setTimeout(() => {
                this.widget.addClass('open');
                this.isOpen = true;
                this.input.focus();
                
                // Send initial greeting if no messages yet
                if (this.messagesContainer.find('.est-chatbot-message').length === 0) {
                    setTimeout(() => {
                        this.addBotMessage("Hello! I'm here to help you with est Financial services. I can provide information about our services or help you book an appointment. What would you like to do?", ["Learn about services", "Book an appointment"]);
                    }, 500);
                }
            }, 10);
        }
        
        closeWidget() {
            this.widget.removeClass('open');
            setTimeout(() => {
                this.widget.hide();
                this.isOpen = false;
            }, 300);
        }
        
        async sendMessage() {
            const message = this.input.val().trim();
            if (!message || this.isTyping) return;
            
            this.addUserMessage(message);
            this.input.val('');
            this.showTypingIndicator();
            
            try {
                const response = await $.ajax({
                    url: est_chatbot_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'est_chatbot_chat',
                        nonce: est_chatbot_ajax.nonce,
                        message: message,
                        session: this.session
                    }
                });
                
                if (response.success && response.data) {
                    this.session = response.data.session || {};
                    this.hideTypingIndicator();
                    this.addBotMessage(response.data.response, response.data.options);
                } else {
                    this.hideTypingIndicator();
                    this.addBotMessage("I'm sorry, I encountered an error. Please try again or contact us directly at info@est.com.au");
                }
            } catch (error) {
                this.hideTypingIndicator();
                this.addBotMessage("I'm sorry, I'm having trouble connecting. Please try again or contact us directly at info@est.com.au");
            }
        }
        
        addUserMessage(message) {
            const messageHtml = `
                <div class="est-chatbot-message user">
                    <div class="est-chatbot-avatar user">U</div>
                    <div class="est-chatbot-content user">${this.escapeHtml(message)}</div>
                </div>
            `;
            this.messagesContainer.append(messageHtml);
            this.scrollToBottom();
        }
        
        addBotMessage(message, options = []) {
            let optionsHtml = '';
            if (options && options.length > 0) {
                optionsHtml = `
                    <div class="est-chatbot-options">
                        ${options.map(option => `
                            <button class="est-chatbot-option">${this.escapeHtml(option)}</button>
                        `).join('')}
                    </div>
                `;
            }
            
            const messageHtml = `
                <div class="est-chatbot-message bot">
                    <div class="est-chatbot-avatar bot">E</div>
                    <div class="est-chatbot-content bot">
                        ${this.formatMessage(message)}
                        ${optionsHtml}
                    </div>
                </div>
            `;
            this.messagesContainer.append(messageHtml);
            this.scrollToBottom();
        }
        
        selectOption(option) {
            this.input.val(option);
            this.sendMessage();
        }
        
        showTypingIndicator() {
            this.isTyping = true;
            this.sendButton.prop('disabled', true);
            
            const typingHtml = `
                <div class="est-chatbot-message bot est-chatbot-typing-indicator">
                    <div class="est-chatbot-avatar bot">E</div>
                    <div class="est-chatbot-typing">
                        <div class="est-chatbot-typing-dot"></div>
                        <div class="est-chatbot-typing-dot"></div>
                        <div class="est-chatbot-typing-dot"></div>
                    </div>
                </div>
            `;
            this.messagesContainer.append(typingHtml);
            this.scrollToBottom();
        }
        
        hideTypingIndicator() {
            this.isTyping = false;
            this.sendButton.prop('disabled', false);
            this.messagesContainer.find('.est-chatbot-typing-indicator').remove();
        }
        
        formatMessage(message) {
            // Convert markdown-style bold text
            message = message.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            // Convert line breaks
            message = message.replace(/\n/g, '<br>');
            return message;
        }
        
        escapeHtml(text) {
            const div = $('<div>').text(text);
            return div.html();
        }
        
        scrollToBottom() {
            setTimeout(() => {
                this.messagesContainer.scrollTop(this.messagesContainer[0].scrollHeight);
            }, 100);
        }
        
        setThemeColor() {
            // Get theme color from WordPress customizer or default
            const themeColor = $('body').data('est-chatbot-color') || '#e53e3e';
            document.documentElement.style.setProperty('--est-chatbot-color', themeColor);
        }
        
        setPosition() {
            // Get position setting from WordPress
            const position = $('body').data('est-chatbot-position') || 'bottom-right';
            this.container.addClass(position);
        }
    }
    
    // Initialize the chatbot widget
    new EstChatbotWidget();
    
})(jQuery);

