<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="/admin/blog" class="p-2 text-neutral-400 hover:text-neutral-600 rounded-lg hover:bg-neutral-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Create Post</h1>
        </div>
    </div>

    <form method="POST" action="/admin/blog" enctype="multipart/form-data" id="postForm">
        <?= csrf_field() ?>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <!-- AI Generate Panel -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-purple-900">AI Content Generator</h3>
                            <p class="text-sm text-purple-700">Generate blog content with AI assistance</p>
                        </div>
                        <button type="button" onclick="toggleAIPanel()" class="ml-auto text-purple-600 hover:text-purple-800">
                            <svg id="aiPanelToggle" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    <div id="aiPanel" class="hidden space-y-4 pt-3 border-t border-purple-200">
                        <div>
                            <label class="block text-sm font-medium text-purple-800 mb-1">Topic / Title</label>
                            <input type="text" id="aiTopic" placeholder="e.g., 10 Tips for Better Photography"
                                   class="w-full px-3 py-2 border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-purple-800 mb-1">Tone</label>
                                <select id="aiTone" class="w-full px-3 py-2 border border-purple-300 rounded-lg">
                                    <option value="professional">Professional</option>
                                    <option value="casual">Casual</option>
                                    <option value="friendly">Friendly</option>
                                    <option value="authoritative">Authoritative</option>
                                    <option value="humorous">Humorous</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-800 mb-1">Length</label>
                                <select id="aiLength" class="w-full px-3 py-2 border border-purple-300 rounded-lg">
                                    <option value="short">Short (~500 words)</option>
                                    <option value="medium" selected>Medium (~1000 words)</option>
                                    <option value="long">Long (~2000 words)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-purple-800 mb-1">Keywords</label>
                                <input type="text" id="aiKeywords" placeholder="SEO keywords..."
                                       class="w-full px-3 py-2 border border-purple-300 rounded-lg">
                            </div>
                        </div>
                        <button type="button" onclick="generateWithAI()" id="aiGenerateBtn"
                                class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Generate Content
                        </button>
                    </div>
                </div>

                <!-- Title -->
                <div class="bg-white rounded-lg shadow p-4">
                    <input type="text" name="title" id="title" required
                           value="<?= e($_SESSION['old']['title'] ?? '') ?>"
                           placeholder="Post title..."
                           class="w-full text-2xl font-bold border-0 focus:ring-0 p-0 placeholder-neutral-300">
                </div>

                <!-- Content Editor -->
                <div class="bg-white rounded-lg shadow">
                    <div class="border-b px-4 py-2 flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="formatDoc('bold')" class="p-2 hover:bg-neutral-100 rounded" title="Bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
                        </button>
                        <button type="button" onclick="formatDoc('italic')" class="p-2 hover:bg-neutral-100 rounded" title="Italic">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 4h-9M14 20H5M15 4L9 20"/></svg>
                        </button>
                        <button type="button" onclick="formatDoc('underline')" class="p-2 hover:bg-neutral-100 rounded" title="Underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3M4 21h16"/></svg>
                        </button>
                        <div class="w-px h-6 bg-neutral-200 mx-1"></div>
                        <button type="button" onclick="formatDoc('insertUnorderedList')" class="p-2 hover:bg-neutral-100 rounded" title="Bullet List">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                        </button>
                        <button type="button" onclick="formatDoc('insertOrderedList')" class="p-2 hover:bg-neutral-100 rounded" title="Numbered List">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6h11M10 12h11M10 18h11M4 6h1v4M4 10h2M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"/></svg>
                        </button>
                        <div class="w-px h-6 bg-neutral-200 mx-1"></div>
                        <select onchange="formatHeading(this.value)" class="px-2 py-1 border rounded text-sm">
                            <option value="">Heading</option>
                            <option value="h2">Heading 2</option>
                            <option value="h3">Heading 3</option>
                            <option value="h4">Heading 4</option>
                            <option value="p">Paragraph</option>
                        </select>
                        <div class="w-px h-6 bg-neutral-200 mx-1"></div>
                        <button type="button" onclick="insertLink()" class="p-2 hover:bg-neutral-100 rounded" title="Insert Link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                        </button>
                        <button type="button" onclick="insertImage()" class="p-2 hover:bg-neutral-100 rounded" title="Insert Image">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </button>
                        <div class="ml-auto flex items-center gap-2">
                            <button type="button" onclick="improveWithAI('expand')" class="text-xs px-2 py-1 text-purple-600 hover:bg-purple-50 rounded" title="AI Expand">
                                Expand
                            </button>
                            <button type="button" onclick="improveWithAI('summarize')" class="text-xs px-2 py-1 text-purple-600 hover:bg-purple-50 rounded" title="AI Summarize">
                                Summarize
                            </button>
                            <button type="button" onclick="improveWithAI('improve')" class="text-xs px-2 py-1 text-purple-600 hover:bg-purple-50 rounded" title="AI Improve">
                                Improve
                            </button>
                        </div>
                    </div>
                    <div id="editor" contenteditable="true"
                         class="min-h-[400px] p-4 focus:outline-none prose max-w-none"
                         oninput="updateContent()"></div>
                    <textarea name="content" id="contentInput" class="hidden" required><?= e($_SESSION['old']['content'] ?? '') ?></textarea>
                </div>

                <!-- Excerpt -->
                <div class="bg-white rounded-lg shadow p-4">
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Excerpt</label>
                    <textarea name="excerpt" rows="3" placeholder="Brief summary of the post (auto-generated if left empty)"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"><?= e($_SESSION['old']['excerpt'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:w-80 space-y-6">
                <!-- Publish Box -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">Publish</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border rounded-lg">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="scheduled">Scheduled</option>
                            </select>
                        </div>
                        <div id="scheduleField" class="hidden">
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Schedule Date</label>
                            <input type="datetime-local" name="scheduled_at" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" class="rounded">
                            <label for="is_featured" class="text-sm text-neutral-700">Featured post</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="allow_comments" id="allow_comments" value="1" checked class="rounded">
                            <label for="allow_comments" class="text-sm text-neutral-700">Allow comments</label>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t flex gap-2">
                        <button type="submit" name="status" value="draft" class="flex-1 px-4 py-2 border rounded-lg hover:bg-neutral-50">
                            Save Draft
                        </button>
                        <button type="submit" name="status" value="published" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Publish
                        </button>
                    </div>
                </div>

                <!-- Category -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">Category</h3>
                    <select name="category_id" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $id => $name): ?>
                        <option value="<?= $id ?>"><?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tags -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">Tags</h3>
                    <input type="text" name="tags" id="tagsInput" placeholder="Add tags separated by commas..."
                           class="w-full px-3 py-2 border rounded-lg mb-2">
                    <div class="flex flex-wrap gap-1">
                        <?php foreach (array_slice($tags, 0, 10) as $tag): ?>
                        <button type="button" onclick="addTag('<?= e($tag->name) ?>')"
                                class="px-2 py-1 text-xs bg-neutral-100 hover:bg-neutral-200 rounded">
                            <?= e($tag->name) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">Featured Image</h3>
                    <div id="imagePreview" class="hidden mb-3">
                        <img src="" alt="" class="w-full h-40 object-cover rounded-lg">
                    </div>
                    <input type="file" name="featured_image" accept="image/*" onchange="previewImage(this)"
                           class="w-full text-sm">
                    <input type="text" name="featured_image_alt" placeholder="Alt text..."
                           class="w-full mt-2 px-3 py-2 border rounded-lg text-sm">
                </div>

                <!-- SEO -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">SEO Settings</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Meta Title</label>
                            <input type="text" name="meta_title" maxlength="70"
                                   class="w-full px-3 py-2 border rounded-lg text-sm"
                                   placeholder="Leave empty to use post title">
                            <div class="text-xs text-neutral-400 mt-1"><span id="metaTitleCount">0</span>/70</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Meta Description</label>
                            <textarea name="meta_description" rows="2" maxlength="160"
                                      class="w-full px-3 py-2 border rounded-lg text-sm"
                                      placeholder="Leave empty to use excerpt"></textarea>
                            <div class="text-xs text-neutral-400 mt-1"><span id="metaDescCount">0</span>/160</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Focus Keywords</label>
                            <input type="text" name="meta_keywords"
                                   class="w-full px-3 py-2 border rounded-lg text-sm"
                                   placeholder="keyword1, keyword2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Initialize editor
document.addEventListener('DOMContentLoaded', function() {
    const content = document.getElementById('contentInput').value;
    if (content) {
        document.getElementById('editor').innerHTML = content;
    }

    // Status change handler
    document.querySelector('select[name="status"]').addEventListener('change', function() {
        document.getElementById('scheduleField').classList.toggle('hidden', this.value !== 'scheduled');
    });

    // Meta counters
    document.querySelector('input[name="meta_title"]').addEventListener('input', function() {
        document.getElementById('metaTitleCount').textContent = this.value.length;
    });
    document.querySelector('textarea[name="meta_description"]').addEventListener('input', function() {
        document.getElementById('metaDescCount').textContent = this.value.length;
    });
});

function updateContent() {
    document.getElementById('contentInput').value = document.getElementById('editor').innerHTML;
}

function formatDoc(cmd) {
    document.execCommand(cmd, false, null);
    document.getElementById('editor').focus();
}

function formatHeading(tag) {
    if (tag) {
        document.execCommand('formatBlock', false, tag);
    }
    document.getElementById('editor').focus();
}

function insertLink() {
    const url = prompt('Enter URL:');
    if (url) {
        document.execCommand('createLink', false, url);
    }
}

function insertImage() {
    const url = prompt('Enter image URL:');
    if (url) {
        document.execCommand('insertImage', false, url);
    }
}

function addTag(tag) {
    const input = document.getElementById('tagsInput');
    const current = input.value.split(',').map(t => t.trim()).filter(t => t);
    if (!current.includes(tag)) {
        current.push(tag);
        input.value = current.join(', ');
    }
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleAIPanel() {
    const panel = document.getElementById('aiPanel');
    const toggle = document.getElementById('aiPanelToggle');
    panel.classList.toggle('hidden');
    toggle.classList.toggle('rotate-180');
}

async function generateWithAI() {
    const btn = document.getElementById('aiGenerateBtn');
    const topic = document.getElementById('aiTopic').value;
    const tone = document.getElementById('aiTone').value;
    const length = document.getElementById('aiLength').value;
    const keywords = document.getElementById('aiKeywords').value;

    if (!topic) {
        alert('Please enter a topic');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg> Generating...';

    try {
        const response = await fetch('/admin/blog/ai/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ topic, tone, length, keywords })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('title').value = data.data.title || topic;
            document.getElementById('editor').innerHTML = data.data.content || '';
            updateContent();

            if (data.data.tags) {
                document.getElementById('tagsInput').value = data.data.tags;
            }
            if (data.data.excerpt) {
                document.querySelector('textarea[name="excerpt"]').value = data.data.excerpt;
            }
            if (data.data.meta_description) {
                document.querySelector('textarea[name="meta_description"]').value = data.data.meta_description;
            }
        } else {
            alert(data.error || 'Failed to generate content');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }

    btn.disabled = false;
    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Generate Content';
}

async function improveWithAI(action) {
    const editor = document.getElementById('editor');
    const selection = window.getSelection();
    const content = selection.toString() || editor.innerHTML;

    if (!content.trim()) {
        alert('Please write or select some content first');
        return;
    }

    try {
        const response = await fetch('/admin/blog/ai/improve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({ content, action })
        });

        const data = await response.json();

        if (data.success && data.data.content) {
            if (selection.toString()) {
                document.execCommand('insertText', false, data.data.content);
            } else {
                editor.innerHTML = data.data.content;
            }
            updateContent();
        } else {
            alert(data.error || 'Failed to improve content');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
