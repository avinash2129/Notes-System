<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notes System</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #1a202c; min-height: 100vh; }
        .container { max-width: 1280px; margin: 0 auto; padding: 32px 16px; }
        .header { background: rgba(255, 255, 255, 0.95); padding: 32px; border-radius: 20px; margin-bottom: 32px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15); display: flex; flex-wrap: wrap; gap: 24px; justify-content: space-between; align-items: center; }
        .header-content h1 { margin: 0; font-size: 2.5rem; color: #1a202c; font-weight: 700; }
        .header-content p { margin: 8px 0 0; font-size: 1.05rem; color: #666; }
        .header-actions { display: flex; gap: 12px; }
        .card { background: rgba(255, 255, 255, 0.98); border: none; border-radius: 16px; box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1); padding: 32px; margin-bottom: 24px; backdrop-filter: blur(10px); }
        .card h2 { margin: 0 0 24px 0; font-size: 1.5rem; color: #1a202c; font-weight: 600; padding-bottom: 12px; border-bottom: 2px solid #f0f4f8; }
        .card h3 { margin: 0; font-size: 1.1rem; color: #2d3748; font-weight: 600; }
        .grid { display: grid; gap: 20px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, minmax(300px, 1fr)); }
        .form-group { display: flex; flex-direction: column; margin-bottom: 20px; }
        .form-group label { font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; color: #2d3748; }
        .form-row { display: flex; gap: 20px; align-items: flex-end; }
        .form-row > div { flex: 1; min-width: 250px; }
        .button { border: none; border-radius: 10px; padding: 12px 20px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; outline: none; display: inline-flex; align-items: center; gap: 8px; }
        .button-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .button-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4); }
        .button-secondary { background: #f0f4f8; color: #2d3748; border: 1px solid #cbd5e1; }
        .button-secondary:hover { background: #e2e8f0; transform: translateY(-2px); }
        .button-danger { background: #f56565; color: #fff; }
        .button-danger:hover { background: #e53e3e; transform: translateY(-2px); }
        .button-success { background: #48bb78; color: #fff; }
        .button-success:hover { background: #38a169; transform: translateY(-2px); }
        .button-small { padding: 8px 14px; font-size: 0.85rem; }
        .button:disabled { opacity: 0.6; cursor: not-allowed; }
        .input, .textarea { width: 100%; border-radius: 10px; border: 1.5px solid #cbd5e1; padding: 12px; font-size: 0.95rem; background: #f7fafc; color: #1a202c; font-family: inherit; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .input:focus, .textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); background: #fff; }
        .textarea { min-height: 140px; resize: vertical; }
        .note-card { display: flex; flex-direction: column; gap: 16px; padding: 20px; background: #f7fafc; border-radius: 12px; border: 1px solid #e2e8f0; transition: all 0.2s ease; }
        .note-card:hover { box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); border-color: #cbd5e1; }
        .note-title { margin: 0; font-size: 1.15rem; color: #1a202c; font-weight: 700; }
        .note-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.85rem; color: #718096; margin-top: 8px; }
        .note-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .status { font-weight: 600; color: #4a5568; }
        .hidden { display: none !important; }
        .spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #ffffff; border-radius: 50%; animation: spin 0.6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .message { padding: 16px; border-radius: 10px; margin-top: 16px; font-weight: 500; }
        .message.error { background: #fed7d7; color: #c53030; border: 1px solid #fc8181; }
        .message.success { background: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
        .summary-box { background: #edf2f7; border-left: 4px solid #667eea; padding: 14px; border-radius: 8px; margin-top: 12px; font-size: 0.95rem; color: #2d3748; line-height: 1.6; }
        .summary-box em { color: #718096; }
        .note-content { white-space: pre-wrap; color: #2d3748; line-height: 1.6; font-size: 0.95rem; margin-top: 12px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; border-radius: 16px; padding: 32px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2); }
        .modal-header { font-size: 1.5rem; font-weight: 700; margin-bottom: 24px; color: #1a202c; }
        .modal-close { position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 28px; cursor: pointer; color: #718096; }
        .notes-grid { display: grid; gap: 20px; }
        .notes-info { text-align: center; padding: 40px; color: #718096; font-size: 1.1rem; }
        .badge { display: inline-block; background: #edf2f7; color: #2d3748; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .icon { font-size: 1.2em; }
        @media (max-width: 768px) { 
            .header { flex-direction: column; align-items: flex-start; }
            .header-content h1 { font-size: 2rem; }
            .form-row { flex-direction: column; }
            .form-row > div { min-width: auto; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .card { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <h1>📝 Notes System</h1>
                <p>Create, search, update, delete, and summarize notes using AI-powered semantic search.</p>
            </div>
            <div class="header-actions">
                <button id="refreshButton" class="button button-secondary">🔄 Refresh</button>
            </div>
        </div>

        <div class="card">
            <h2>✏️ Create New Note</h2>
            <div class="grid grid-2">
                <div class="form-group">
                    <label for="title">📌 Title</label>
                    <input id="title" class="input" placeholder="Enter a meaningful title..." />
                </div>
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <div class="form-group">
                        <label for="searchQuery">🔍 Search Notes</label>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input id="searchQuery" class="input" placeholder="Search by keywords or topic..." />
                            <button id="searchButton" class="button button-primary button-small">Search</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="content">📄 Content</label>
                <textarea id="content" class="textarea" placeholder="Write your note content here. You can use multiple paragraphs..."></textarea>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <button id="saveButton" class="button button-primary">💾 Save Note</button>
                <button id="clearButton" class="button button-secondary button-small" style="display:none;">Clear Form</button>
                <span id="formStatus" class="status"></span>
            </div>
            <div id="formMessage" class="message hidden"></div>
        </div>

        <div class="card">
            <div id="searchBanner" style="display:none; background:#e3f2fd; border-left:4px solid #667eea; padding:16px; border-radius:8px; margin-bottom:20px;">
                <strong style="color:#1e40af;">🔍 Search Results</strong>
                <span id="searchBannerText" style="color:#2d3748; margin-left:12px;"></span>
                <button id="clearSearchButton" class="button button-small button-secondary" style="margin-left:12px; float:right;">✕ Clear Search</button>
            </div>
            <h2>📚 Your Notes</h2>
            <div id="notesListContainer">
                <div id="notesEmpty" class="notes-info">📭 No notes yet. Start by creating a new note above!</div>
                <div id="notesList" class="notes-grid"></div>
            </div>
            <div style="margin-top: 24px; display:flex; gap:12px; flex-wrap:wrap; align-items:center; justify-content:space-between;">
                <div>
                    <button id="loadMoreButton" class="button button-secondary button-small hidden">Load More Notes</button>
                </div>
                <span id="notesInfo" class="status"></span>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeEditModal()">✕</button>
            <div class="modal-header">✏️ Edit Note</div>
            <div class="form-group">
                <label for="modalTitle">Title</label>
                <input id="modalTitle" class="input" placeholder="Note title" />
            </div>
            <div class="form-group">
                <label for="modalContent">Content</label>
                <textarea id="modalContent" class="textarea" placeholder="Note content"></textarea>
            </div>
            <div style="display:flex; gap:12px; margin-top:24px;">
                <button id="modalSaveButton" class="button button-primary">Save Changes</button>
                <button class="button button-secondary" onclick="closeEditModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        const apiBase = '/api/notes';
        let currentPage = 1;
        let lastSearch = '';
        let loadMoreEnabled = true;
        let editingNoteId = null;

        const titleInput = document.getElementById('title');
        const contentInput = document.getElementById('content');
        const clearButton = document.getElementById('clearButton');
        const searchInput = document.getElementById('searchQuery');
        const saveButton = document.getElementById('saveButton');
        const searchButton = document.getElementById('searchButton');
        const refreshButton = document.getElementById('refreshButton');
        const notesList = document.getElementById('notesList');
        const notesEmpty = document.getElementById('notesEmpty');
        const loadMoreButton = document.getElementById('loadMoreButton');
        const notesInfo = document.getElementById('notesInfo');
        const formStatus = document.getElementById('formStatus');
        const formMessage = document.getElementById('formMessage');
        const editModal = document.getElementById('editModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        const modalSaveButton = document.getElementById('modalSaveButton');
        const searchBanner = document.getElementById('searchBanner');
        const searchBannerText = document.getElementById('searchBannerText');
        const clearSearchButton = document.getElementById('clearSearchButton');

        function setLoading(element, loading) {
            if (loading) {
                element.dataset.originalText = element.textContent;
                element.innerHTML = '<span class="spinner"></span> ' + (element.dataset.originalText || '');
                element.disabled = true;
            } else {
                element.textContent = element.dataset.originalText || element.textContent;
                element.disabled = false;
            }
        }

        function showMessage(element, message, type = 'error') {
            element.textContent = message;
            element.className = type === 'error' ? 'message error' : 'message success';
            element.classList.remove('hidden');
        }

        function hideMessage(element) {
            element.textContent = '';
            element.className = 'message hidden';
        }

        function closeEditModal() {
            editModal.classList.remove('active');
            editingNoteId = null;
        }

        async function request(url, options = {}) {
            const response = await fetch(url, {
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                ...options,
            });

            const body = await response.json().catch(() => null);
            if (!response.ok) {
                throw body || { message: 'Request failed' };
            }
            return body;
        }

        function formatDate(value) {
            if (!value) return '-';
            const date = new Date(value);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderNote(note) {
            const card = document.createElement('div');
            card.className = 'card note-card';

            card.innerHTML = `
                <div>
                    <h3 class="note-title">${escapeHtml(note.title)}</h3>
                    <div class="note-meta">
                        <span>📅 ${formatDate(note.created_at)}</span>
                        <span>✏️ ${formatDate(note.updated_at)}</span>
                    </div>
                </div>
                <div class="note-content">${escapeHtml(note.content.substring(0, 200))}${note.content.length > 200 ? '...' : ''}</div>
                <div class="summary-box">
                    <strong>🤖 Summary:</strong> ${note.summary ? escapeHtml(note.summary) : '<em>Generate a summary</em>'}
                </div>
                <div class="note-actions">
                    <button class="button button-small button-secondary" data-action="summary">💡 Generate Summary</button>
                    <button class="button button-small button-secondary" data-action="view">👁️ View Full</button>
                    <button class="button button-small button-secondary" data-action="edit">✏️ Edit</button>
                    <button class="button button-small button-danger" data-action="delete">🗑️ Delete</button>
                </div>
            `;

            const summaryButton = card.querySelector('[data-action="summary"]');
            const viewButton = card.querySelector('[data-action="view"]');
            const editButton = card.querySelector('[data-action="edit"]');
            const deleteButton = card.querySelector('[data-action="delete"]');
            const summaryDisplay = card.querySelector('.summary-box');

            summaryButton.addEventListener('click', async () => {
                try {
                    setLoading(summaryButton, true);
                    const data = await request(`${apiBase}/${note.id}/summary`, { method: 'POST' });
                    summaryDisplay.innerHTML = `<strong>🤖 Summary:</strong> ${escapeHtml(data.summary)}`;
                    showStatus('✅ Summary generated successfully!', 'success');
                } catch (err) {
                    showStatus('❌ ' + (err.message || 'Unable to generate summary.'));
                } finally {
                    setLoading(summaryButton, false);
                }
            });

            viewButton.addEventListener('click', () => {
                alert(escapeHtml(note.content));
            });

            editButton.addEventListener('click', () => {
                editingNoteId = note.id;
                modalTitle.value = note.title;
                modalContent.value = note.content;
                editModal.classList.add('active');
            });

            deleteButton.addEventListener('click', async () => {
                if (!confirm('Are you sure you want to delete this note?')) return;
                try {
                    setLoading(deleteButton, true);
                    await request(`${apiBase}/${note.id}`, { method: 'DELETE' });
                    card.remove();
                    showStatus('✅ Note deleted successfully!', 'success');
                    await loadNotes(true);
                } catch (err) {
                    showStatus('❌ ' + (err.message || 'Unable to delete note.'));
                } finally {
                    setLoading(deleteButton, false);
                }
            });

            return card;
        }

        function showStatus(message, type = 'error') {
            formStatus.textContent = message;
            formStatus.style.color = type === 'success' ? '#22543d' : '#c53030';
            setTimeout(() => {
                formStatus.textContent = '';
            }, 5000);
        }

        async function loadNotes(reset = false) {
            if (reset) {
                currentPage = 1;
                notesList.innerHTML = '';
                loadMoreEnabled = true;
            }

            try {
                const response = await request(`${apiBase}?page=${currentPage}&limit=9`);
                const notes = response.data || [];

                notes.forEach(note => notesList.appendChild(renderNote(note)));
                notesEmpty.style.display = notesList.children.length === 0 ? 'block' : 'none';
                loadMoreEnabled = response.next_page_url !== null;
                loadMoreButton.classList.toggle('hidden', !loadMoreEnabled);
                notesInfo.textContent = `📊 Showing ${notesList.children.length} note${notesList.children.length !== 1 ? 's' : ''}`;
                if (loadMoreEnabled) {
                    notesInfo.textContent += ` (Page ${currentPage})`;
                }
            } catch (err) {
                showMessage(formMessage, '❌ ' + (err.message || 'Unable to load notes.'));
            }
        }

        async function searchNotes() {
            const query = searchInput.value.trim();
            if (!query) {
                showStatus('❌ Please enter a search query.');
                return;
            }

            try {
                setLoading(searchButton, true);
                const response = await request(`${apiBase}/search`, {
                    method: 'POST',
                    body: JSON.stringify({ q: query, limit: 20 }),
                });

                notesList.innerHTML = '';
                response.results.forEach(note => notesList.appendChild(renderNote(note)));
                notesEmpty.style.display = response.results.length === 0 ? 'block' : 'none';
                loadMoreButton.classList.add('hidden');
                
                // Show search banner
                searchBanner.style.display = 'block';
                searchBannerText.innerHTML = `Found <strong>${response.count}</strong> result${response.count !== 1 ? 's' : ''} for "<strong>${escapeHtml(query)}</strong>"`;
                notesInfo.textContent = '';
                lastSearch = query;
                showStatus('✅ Search complete!', 'success');
            } catch (err) {
                showMessage(formMessage, '❌ ' + (err.message || 'Search failed.'));
            } finally {
                setLoading(searchButton, false);
            }
        }

        async function saveNote() {
            const title = titleInput.value.trim();
            const content = contentInput.value.trim();

            if (!title || !content) {
                showMessage(formMessage, '❌ Title and content are required.');
                return;
            }

            try {
                hideMessage(formMessage);
                setLoading(saveButton, true);
                await request(apiBase, {
                    method: 'POST',
                    body: JSON.stringify({ title, content })
                });

                titleInput.value = '';
                contentInput.value = '';
                clearButton.style.display = 'none';
                showStatus('✅ Note created successfully!', 'success');
                await loadNotes(true);
            } catch (err) {
                showMessage(formMessage, '❌ ' + (err.message || 'Unable to save note.'));
            } finally {
                setLoading(saveButton, false);
            }
        }

        async function updateNote() {
            const title = modalTitle.value.trim();
            const content = modalContent.value.trim();

            if (!title || !content) {
                showStatus('❌ Title and content are required.');
                return;
            }

            try {
                setLoading(modalSaveButton, true);
                await request(`${apiBase}/${editingNoteId}`, {
                    method: 'PUT',
                    body: JSON.stringify({ title, content })
                });

                closeEditModal();
                showStatus('✅ Note updated successfully!', 'success');
                await loadNotes(true);
            } catch (err) {
                showStatus('❌ ' + (err.message || 'Unable to update note.'));
            } finally {
                setLoading(modalSaveButton, false);
            }
        }

        saveButton.addEventListener('click', saveNote);
        clearButton.addEventListener('click', () => {
            titleInput.value = '';
            contentInput.value = '';
            clearButton.style.display = 'none';
        });
        searchButton.addEventListener('click', searchNotes);
        refreshButton.addEventListener('click', () => {
            searchBanner.style.display = 'none';
            searchInput.value = '';
            loadNotes(true);
        });
        clearSearchButton.addEventListener('click', () => {
            searchBanner.style.display = 'none';
            searchInput.value = '';
            loadNotes(true);
        });
        loadMoreButton.addEventListener('click', async () => {
            currentPage += 1;
            await loadNotes(false);
        });
        modalSaveButton.addEventListener('click', updateNote);

        // Show clear button when typing
        titleInput.addEventListener('input', () => {
            clearButton.style.display = titleInput.value || contentInput.value ? 'inline-flex' : 'none';
        });
        contentInput.addEventListener('input', () => {
            clearButton.style.display = titleInput.value || contentInput.value ? 'inline-flex' : 'none';
        });

        // Close modal on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && editModal.classList.contains('active')) {
                closeEditModal();
            }
        });

        document.addEventListener('DOMContentLoaded', () => loadNotes(true));
    </script>
</body>
</html>
