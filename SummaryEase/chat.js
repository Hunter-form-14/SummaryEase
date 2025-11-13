document.addEventListener('DOMContentLoaded', () => {
    // HTML要素を取得
    const messageForm = document.getElementById('message-form');
    const messagesDiv = document.getElementById('messages');
    const summaryContainer = document.getElementById('categorized-summary');
    const updateSummaryBtn = document.getElementById('update-summary');
    const resetSummaryBtn = document.getElementById('reset-summary');
    const resetConversationBtn = document.getElementById('reset-conversation');
    const addItemForm = document.getElementById('add-item-form');
    const savedItemsList = document.getElementById('saved-items-list');
    const usernameElement = document.getElementById('username');
    const roomnameElement = document.getElementById('roomname');

    // ページ読み込み時に取得
    fetchUsername();
    fetchRoomname();
    fetchSummary();

    // イベントリスナーを設定
    messageForm.addEventListener('submit', sendMessage);
    updateSummaryBtn.addEventListener('click', updateSummary);
    resetSummaryBtn.addEventListener('click', resetSummary);
    resetConversationBtn.addEventListener('click', resetConversation);
    addItemForm.addEventListener('submit', addItem);

    // 100ミリ秒ごとに関数を実行
    setInterval(Update, 100);

    // 他のユーザーとの同期
    async function Update() {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'get_update' })
            });
            const data = await response.json();
            if (data.success) {
                messagesDiv.innerHTML = '';
                data.messages.forEach(displayMessage);
            } else {
                console.error(data.error || '更新に失敗しました。');
            }
        } catch (error) {
            console.error('エラー:', error);
        }
    }

    // ユーザー名を取得
    async function fetchUsername() {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'get_username' })
            });
            const data = await response.json();
            if (data.success) {
                usernameElement.textContent = data.username;
            } else {
                usernameElement.textContent = 'ゲスト';
                console.warn(data.error || 'セッション情報が見つからないため、ゲストとして扱います。');
            }
        } catch (error) {
            console.error('エラー:', error);
            usernameElement.textContent = 'ゲスト';
        }
    }

    // ルーム名を取得
    async function fetchRoomname() {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'get_roomname' })
            });
            const data = await response.json();
            if (data.success) {
                roomnameElement.textContent = data.roomname;
            } else {
                usernameElement.textContent = '不明';
                console.warn(data.error || 'セッション情報が見つからないため、不明として扱います。');
            }
        } catch (error) {
            console.error('エラー:', error);
            usernameElement.textContent = '不明';
        }
    }

    // 要約を取得
    async function fetchSummary() {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'get_summary' })
            });
            const data = await response.json();
            if (data.success && data.summary) {
                displayCategorizedSummary(data.summary);
            } else {
                displayCategorizedSummary(null);
            }
        } catch (error) {
            console.error('エラー:', error);
            displayCategorizedSummary(null);
        }
    }

    // メッセージを送信
    async function sendMessage(event) {
        event.preventDefault();
        const username = usernameElement.textContent.trim();
        const message = document.getElementById('message').value.trim();
        if (username && message) {
            try {
                const response = await fetch('chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'send_message',
                        username: username,
                        message: message
                    })
                });
                const data = await response.json();
                if (data.success) {
                    displayMessage(data.message);
                    document.getElementById('message').value = '';
                } else {
                    throw new Error(data.error || 'メッセージの送信に失敗しました。');
                }
            } catch (error) {
                console.error('エラー:', error);
                alert(error.message);
            }
        }
    }

    // メッセージを表示
    function displayMessage(message) {
        const messageElement = document.createElement('div');
        messageElement.textContent = `${message.username}: ${message.message_text}`;
        messagesDiv.appendChild(messageElement);
    }

    // 要約を更新
    async function updateSummary() {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'update_summary' })
            });
            const data = await response.json();
            if (data.success) {
                summaryContainer.innerHTML = '';
                displayCategorizedSummary(data.summary);
            } else {
                throw new Error(data.error || '要約の更新に失敗しました。');
            }
        } catch (error) {
            console.error('エラー:', error);
            alert(error.message);
        }
    }

    // カテゴリごとの要約を表示
    function displayCategorizedSummary(summary) {
        const messageElement = document.createElement('div');
        if (summary) {
            const formattedSummary = summary.split('。').join('。<br>');
            messageElement.innerHTML = formattedSummary;
        } else {
            messageElement.textContent = '要約はありません。';
        }
        summaryContainer.appendChild(messageElement);
    }

    // 要約をリセット
    async function resetSummary() {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'reset_summary' })
            });
            const data = await response.json();
            if (data.success) {
                document.getElementById('categorized-summary').innerHTML = '';
            } else {
                throw new Error(data.error || '要約のリセットに失敗しました。');
            }
        } catch (error) {
            console.error('エラー:', error);
            alert(error.message);
        }
    }

    // 会話をリセット
    async function resetConversation() {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ action: 'reset_conversation' })
            });
            const data = await response.json();
            if (data.success) {
                messagesDiv.innerHTML = '';
                document.getElementById('messages').innerHTML = '';
            } else {
                throw new Error(data.error || '会話のリセットに失敗しました。');
            }
        } catch (error) {
            console.error('エラー:', error);
            alert(error.message);
        }
    }

    // 項目を追加
    async function addItem(event) {
        event.preventDefault();
        const newItem = document.getElementById('new-item').value.trim();
        if (newItem) {
            try {
                const response = await fetch('chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add_item',
                        item: newItem
                    })
                });
                const data = await response.json();
                if (data.success) {
                    updateSavedItemsList(data.items);
                    document.getElementById('new-item').value = '';
                } else {
                    throw new Error(data.error || '項目の追加に失敗しました。');
                }
            } catch (error) {
                console.error('エラー:', error);
                alert(error.message);
            }
        }
    }

    // 保存された項目リストを更新
    function updateSavedItemsList(items) {
        savedItemsList.innerHTML = '';
        items.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            const deleteBtn = document.createElement('button');
            deleteBtn.textContent = '削除';
            deleteBtn.className = 'button4-1';
            deleteBtn.addEventListener('click', () => deleteItem(item));
            li.appendChild(deleteBtn);
            savedItemsList.appendChild(li);
        });
    }

    // 項目を削除
    async function deleteItem(item) {
        try {
            const response = await fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_item',
                    item: item
                })
            });
            const data = await response.json();
            if (data.success) {
                updateSavedItemsList(data.items);
            } else {
                throw new Error(data.error || '項目の削除に失敗しました。');
            }
        } catch (error) {
            console.error('エラー:', error);
            alert(error.message);
        }
    }
});
