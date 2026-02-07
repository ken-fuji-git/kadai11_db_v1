// Main App Logic
document.addEventListener('DOMContentLoaded', () => {
    const startBtn = document.getElementById('start-btn');
    const diaryInput = document.getElementById('diary-input');
    const penguinStage = document.getElementById('penguin-stage');
    const chatOverlay = document.getElementById('chat-overlay');

    startBtn.addEventListener('click', async () => {
        const text = diaryInput.value.trim();
        if (!text) {
            alert('今日のがんばりを入力してね！');
            return;
        }

        // Temporary feedback
        startBtn.textContent = '会議準備中...';
        startBtn.disabled = true;

        try {
            // 1. Save Diary
            console.log('Saving diary...');
            const saveRes = await fetch('api/save_diary.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: text })
            });
            const saveData = await saveRes.json();

            if (!saveData.success) throw new Error(saveData.error || 'Save failed');

            console.log('Diary saved, ID:', saveData.diary_id);
            startBtn.textContent = 'ペンギン集合中...';

            // 2. Generate Chat (Mock or Real)
            console.log('Generating chat...');
            const chatRes = await fetch('api/generate_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ diary_id: saveData.diary_id })
            });
            const chatData = await chatRes.json();

            if (!chatData.success) throw new Error(chatData.error || 'Chat generation failed');

            console.log('Chat generated:', chatData.chat);
            startBtn.classList.add('hidden'); // Hide button after start
            document.querySelector('.input-container').classList.add('hidden'); // Hide input too for focus

            // 3. Play Chat
            playChat(chatData.chat);

        } catch (err) {
            console.error(err);
            alert('エラーが発生しました: ' + err.message);
            startBtn.textContent = '会議を始める';
            startBtn.disabled = false;
        }
    });

    function playChat(chatSequence) {
        let index = 0;

        function showNextMessage() {
            if (index >= chatSequence.length) {
                // End of chat
                const endMsg = document.createElement('div');
                endMsg.className = 'chat-bubble system-msg';
                endMsg.textContent = '会議終了。おつかれさまでした！';
                chatOverlay.appendChild(endMsg);
                return;
            }

            const msg = chatSequence[index];
            createChatBubble(msg);
            index++;

            // Simple delay. 
            // Reading speed approximation: 200ms per character + 1s base
            const delay = Math.min(5000, 1000 + (msg.message.length * 100));
            setTimeout(showNextMessage, delay);
        }

        showNextMessage();
    }

    function createChatBubble(msgData) {
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble';

        // Simple distinct styles for speakers
        const isElder = msgData.speaker.includes('長老');
        if (isElder) bubble.classList.add('elder');

        const nameLabel = document.createElement('div');
        nameLabel.className = 'speaker-name';
        nameLabel.textContent = msgData.speaker;

        const textContent = document.createElement('div');
        textContent.className = 'message-text';
        textContent.textContent = msgData.message;

        bubble.appendChild(nameLabel);
        bubble.appendChild(textContent);

        chatOverlay.appendChild(bubble); // Add to container

        // Auto scroll to bottom
        chatOverlay.scrollTop = chatOverlay.scrollHeight;
    }
});
