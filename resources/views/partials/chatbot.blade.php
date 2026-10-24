<button class="bot-fab" id="bot-fab" title="Travel Assistant" aria-label="Open travel assistant"><i class="fa-solid fa-robot"></i></button>
<div class="bot-panel" id="bot-panel" data-url="{{ route('chatbot') }}" hidden>
    <div class="bot-head">
        <div><b>Travel Assistant</b><span>Online · answers from live TravelBuddy data</span></div>
        <button id="bot-close" class="icon-btn-sm" aria-label="Close chatbot"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="bot-msgs" id="bot-msgs">
        <div class="bot-bubble them">Hi! I can suggest top destinations, list upcoming events, explain booking & reviews, or point you around TravelBuddy. Try a chip below 👇</div>
    </div>
    <div class="bot-chips">
        <button type="button" data-ask="Top destinations">Top destinations</button>
        <button type="button" data-ask="Upcoming events">Upcoming events</button>
        <button type="button" data-ask="How do I book?">How do I book?</button>
        <button type="button" data-ask="Help">Help</button>
    </div>
    <form id="bot-form" class="bot-input">
        <input id="bot-text" type="text" placeholder="Ask me anything…" autocomplete="off">
        <button class="btn-mini" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
</div>