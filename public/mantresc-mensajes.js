const messagesCount = document.querySelector('[data-messages-count]');
const messagesInbox = document.querySelector('[data-messages-inbox]');
const messagesHistory = document.querySelector('[data-messages-scroll]');

if (messagesHistory?.hasAttribute('data-messages-latest')) {
    messagesHistory.scrollTop = messagesHistory.scrollHeight;
}

if (messagesCount) {
    let refreshingMessages = false;

    function showMessageCount(total) {
        messagesCount.textContent = total;
        messagesCount.hidden = total === 0;
    }

    async function refreshMessages() {
        if (document.hidden || refreshingMessages) return;

        refreshingMessages = true;
        try {
            const url = messagesInbox ? window.location.href : messagesCount.dataset.countUrl;
            const response = await fetch(url, { cache: 'no-store' });
            if (!response.ok || response.redirected) return;

            if (!messagesInbox) {
                const { total } = await response.json();
                showMessageCount(total);
                return;
            }

            const updated = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updatedCount = updated.querySelector('[data-messages-count]');
            if (!updatedCount) return;

            showMessageCount(Number(updatedCount.textContent));
            for (const panel of messagesInbox.querySelectorAll('[data-messages-refresh]')) {
                const replacement = updated.querySelector(`[data-messages-refresh="${panel.dataset.messagesRefresh}"]`);
                if (!replacement || panel.innerHTML === replacement.innerHTML || panel.contains(document.activeElement)) continue;
                if (panel === messagesHistory && !panel.hasAttribute('data-messages-latest')) continue;

                const atBottom = panel.scrollHeight - panel.scrollTop - panel.clientHeight < 48;
                panel.replaceChildren(...replacement.childNodes);
                if (panel === messagesHistory && atBottom) panel.scrollTop = panel.scrollHeight;
            }
        } catch {
            // Keep the visible conversation and draft if the connection drops.
        } finally {
            refreshingMessages = false;
        }
    }

    window.setInterval(refreshMessages, 10000);
    document.addEventListener('visibilitychange', refreshMessages);
}
