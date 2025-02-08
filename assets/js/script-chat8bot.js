// Initialize reCAPTCHA, Paste Chat Div
  
jQuery(document).ready(function ($) {
 
    //Add chatbot HTML structure to the body 
    const chatbotHTML = `
    <div class="chatbot-8bot-container">
        <div class="chatbot-toggle-button">${chat8botData.chat8bot_btn_text}</div>
        <div class="chatbot-wrapper hidden">
            <div class="resizable-bar"></div>
            <div class="title">
                ${chat8botData.chat8bot_title}
                <button class="close-btn">×</button>
            </div>
            <div class="msgbox">
                <div class="response server">
                    <div class="icon"></div>
                    <div class="msg">
                        <p>${chat8botData.chat8bot_intro_message}</p>
                    </div>
                </div>
            </div>
            <div class="msg-new">
                <div class="input-field">
                    <input type="text" placeholder="Type your message" required>
                    <button></button>
                </div>
            </div>
            <div id="chat8bot-recaptcha" class="g-recaptcha"></div> 
        </div>
    `;
    $('body').append(chatbotHTML);
    console.log(chatbotHTML);


  
    // Event handlers
    const chatWrapper = $('.chatbot-wrapper');
    const toggleButton = $('.chatbot-toggle-button');
    const closeButton = $('.close-btn');
    const msgInput = $('.input-field input');
    const msgBox = $('.chatbot-wrapper .msgbox');
 
    //Auto Chat height 80%
    var chatHeight = Math.round($(window).height() * 0.7);
    var chatWidth = Math.round($(window).width() * 0.9);
    chatWrapper.css({
        'width': chatWidth + 'px',
    });
    msgBox.css({
        'height': chatHeight + 'px',
    });

    //Toggle chat window
    toggleButton.click(() => {
        chatWrapper.removeClass('hidden');
        toggleButton.addClass('hidden');
    });

    closeButton.click(() => {
        chatWrapper.addClass('hidden');
        toggleButton.removeClass('hidden');
    });

    // Submit Comment 
    $('.input-field button').click(() => {
        const message = msgInput.val().trim();
        if (!message) return;

        // Add user's message to the chat
        msgBox.append(`
            <div class="response visitor">
                <div class="icon"></div>
                <div class="msg"><p>${message}</p></div>
            </div>
        `);

        // Scroll to the bottom of the chat
        msgBox.scrollTop(msgBox[0].scrollHeight);

        // Clear input
        msgInput.val('');

        // Show typing indicator
        const typingIndicator = $(`
            <div class="response typing-indicator">
                <div class="icon"></div>
                <div class="msg"><small><i>thinking...</i></small></div>
            </div>
        `);
        msgBox.append(typingIndicator);

        // Send AJAX request
        $.post(chat8botData.ajax_url, {
            action: 'chat8bot_send_message',
            message,
        }, (response) => {
            // Remove typing indicator
            typingIndicator.remove();

            if (response.success) {
                // Add server's response to the chat
                msgBox.append(`
                    <div class="response server">
                        <div class="icon"></div>
                        <div class="msg"><p>${response.data.response}</p></div>
                    </div>
                `);
            } else {
                // Handle error
                msgBox.append(`
                    <div class="response server">
                        <div class="icon"></div>
                        <div class="msg"><p>Error: ${response.data.error}</p></div>
                    </div>
                `);
            }

            // Scroll to the bottom of the chat
            msgBox.scrollTop(msgBox[0].scrollHeight);
        });
    });
});

// Add this function to handle reCAPTCHA callback
function onChatbotSubmit(token) {
    const message = jQuery('.input-field input').val().trim();
    const msgBox = jQuery('.chatbot-wrapper .msgbox');

    // Add user's message to the chat
    msgBox.append(`
        <div class="response visitor">
            <div class="icon"></div>
            <div class="msg"><p>${message}</p></div>
        </div>
    `);

    // Show typing indicator
    const typingIndicator = jQuery(`
        <div class="response typing-indicator">
            <div class="icon"></div>
            <div class="msg"><small><i>thinking...</i></small></div>
        </div>
    `);
    msgBox.append(typingIndicator);

    // Send AJAX request 
    jQuery.post(chat8botData.ajax_url, {
        action: 'chat8bot_send_message',
        message: message,
        recaptcha_token: token
    }, function (response) {
        // Remove typing indicator
        typingIndicator.remove();

        if (response.success) {
            msgBox.append(`
                <div class="response server">
                    <div class="icon"></div>
                    <div class="msg"><p>${response.data.response}</p></div>
                </div>
            `);
        } else {
            msgBox.append(`
                <div class="response server">
                    <div class="icon"></div>
                    <div class="msg"><p>Error: ${response.data.error}</p></div>
                </div>
            `);
        }

        // Clear input
        jQuery('.input-field input').val('');

        // Scroll to bottom
        msgBox.scrollTop(msgBox[0].scrollHeight);

    });
}