Fix chat reaction '+' button behavior in public/assets.js:
1. Always position the '+' button directly below the bot message bubble (.pax-msg-bot).
2. Prevent horizontal scrolling.
3. Wrap long messages correctly.
4. Keep '+' always visible under the message.
5. Highlight emoji once selected (persist via localStorage).
6. Ensure perfect alignment on mobile and desktop.
