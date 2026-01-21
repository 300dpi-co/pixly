<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="/admin/blog" class="p-2 text-neutral-400 hover:text-neutral-600 rounded-lg hover:bg-neutral-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Edit Post</h1>
            <?php if ($post->status === 'published'): ?>
            <a href="/blog/<?= e($post->slug) ?>" target="_blank" class="text-sm text-primary-600 hover:text-primary-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                View
            </a>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-2 text-sm text-neutral-500">
            <?php if ($post->ai_generated): ?>
            <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded">AI Generated</span>
            <?php endif; ?>
            <span><?= number_format($post->view_count) ?> views</span>
            <span><?= $post->read_time_minutes ?> min read</span>
        </div>
    </div>

    <form method="POST" action="/admin/blog/<?= $post->id ?>" enctype="multipart/form-data" id="postForm">
        <?= csrf_field() ?>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <!-- AI Tools Panel -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-purple-900">AI Tools</h3>
                            <p class="text-sm text-purple-700">Select text and improve with AI</p>
                        </div>
                        <div class="ml-auto flex gap-2">
                            <button type="button" onclick="improveWithAI('expand')" class="px-3 py-1 text-sm text-purple-600 hover:bg-purple-100 rounded">Expand</button>
                            <button type="button" onclick="improveWithAI('summarize')" class="px-3 py-1 text-sm text-purple-600 hover:bg-purple-100 rounded">Summarize</button>
                            <button type="button" onclick="improveWithAI('improve')" class="px-3 py-1 text-sm text-purple-600 hover:bg-purple-100 rounded">Improve</button>
                            <button type="button" onclick="improveWithAI('seo')" class="px-3 py-1 text-sm text-purple-600 hover:bg-purple-100 rounded">SEO Optimize</button>
                        </div>
                    </div>
                </div>

                <!-- Title -->
                <div class="bg-white rounded-lg shadow p-4">
                    <input type="text" name="title" id="title" required
                           value="<?= e($post->title) ?>"
                           placeholder="Post title..."
                           class="w-full text-2xl font-bold border-0 focus:ring-0 p-0 placeholder-neutral-300">
                    <div class="text-sm text-neutral-400 mt-2">
                        Slug: <span class="text-neutral-600">/blog/<?= e($post->slug) ?></span>
                    </div>
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
                    </div>
                    <div id="editor" contenteditable="true"
                         class="min-h-[400px] p-4 focus:outline-none prose max-w-none"
                         oninput="updateContent()"><?= $post->content ?></div>
                    <textarea name="content" id="contentInput" class="hidden" required><?= e($post->content) ?></textarea>
                </div>

                <!-- Excerpt -->
                <div class="bg-white rounded-lg shadow p-4">
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Excerpt</label>
                    <textarea name="excerpt" rows="3" placeholder="Brief summary of the post"
                              class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500"><?= e($post->excerpt) ?></textarea>
                </div>

                <!-- Revisions -->
                <?php if (!empty($revisions)): ?>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-3">Revision History</h3>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <?php foreach (array_slice($revisions, 0, 5) as $rev): ?>
                        <div class="flex items-center justify-between text-sm py-2 border-b last:border-0">
                            <div>
                                <span class="text-neutral-600"><?= e($rev['username'] ?? 'Unknown') ?></span>
                                <span class="text-neutral-400 mx-2">-</span>
                                <span class="text-neutral-500"><?= date('M j, Y g:i A', strtotime($rev['created_at'])) ?></span>
                            </div>
                            <?php if ($rev['revision_note']): ?>
                            <span class="text-xs text-neutral-400"><?= e($rev['revision_note']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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
                                <option value="draft" <?= $post->status === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $post->status === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="scheduled" <?= $post->status === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                                <option value="archived" <?= $post->status === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <div id="scheduleField" class="<?= $post->status === 'scheduled' ? '' : 'hidden' ?>">
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Schedule Date</label>
                            <input type="datetime-local" name="scheduled_at"
                                   value="<?= $post->scheduled_at ? date('Y-m-d\TH:i', strtotime($post->scheduled_at)) : '' ?>"
                                   class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_featured" id="is_featured" value="1" <?= $post->is_featured ? 'checked' : '' ?> class="rounded">
                            <label for="is_featured" class="text-sm text-neutral-700">Featured post</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="allow_comments" id="allow_comments" value="1" <?= $post->allow_comments ? 'checked' : '' ?> class="rounded">
                            <label for="allow_comments" class="text-sm text-neutral-700">Allow comments</label>
                        </div>
                        <?php if ($post->published_at): ?>
                        <div class="text-sm text-neutral-500">
                            Published: <?= date('M j, Y g:i A', strtotime($post->published_at)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            Update
                        </button>
                        <form method="POST" action="/admin/blog/<?= $post->id ?>/delete" onsubmit="return confirm('Delete this post?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg">Delete</button>
                        </form>
                    </div>
                </div>

                <!-- Category -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">Category</h3>
                    <select name="category_id" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $id => $name): ?>
                        <option value="<?= $id ?>" <?= $post->category_id == $id ? 'selected' : '' ?>><?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tags -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">Tags</h3>
                    <input type="text" name="tags" id="tagsInput" value="<?= e($tagString) ?>"
                           placeholder="Add tags separated by commas..."
                           class="w-full px-3 py-2 border rounded-lg mb-2">
                    <div class="flex flex-wrap gap-1">
                        <?php foreach (array_slice($allTags, 0, 10) as $tag): ?>
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
                    <?php if ($post->featured_image): ?>
                    <div class="mb-3 relative">
                        <img src="/uploads/<?= e($post->featured_image) ?>" alt="" class="w-full h-40 object-cover rounded-lg">
                        <label class="absolute bottom-2 right-2 flex items-center gap-1 px-2 py-1 bg-black/50 text-white text-xs rounded cursor-pointer">
                            <input type="checkbox" name="remove_featured_image" value="1" class="rounded">
                            Remove
                        </label>
                    </div>
                    <?php endif; ?>
                    <div id="imagePreview" class="<?= $post->featured_image ? 'hidden' : '' ?> mb-3">
                        <img src="" alt="" class="w-full h-40 object-cover rounded-lg">
                    </div>
                    <input type="file" name="featured_image" accept="image/*" onchange="previewImage(this)"
                           class="w-full text-sm">
                    <input type="text" name="featured_image_alt" value="<?= e($post->featured_image_alt) ?>"
                           placeholder="Alt text..."
                           class="w-full mt-2 px-3 py-2 border rounded-lg text-sm">
                </div>

                <!-- SEO -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-semibold text-neutral-900 mb-4">SEO Settings</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Meta Title</label>
                            <input type="text" name="meta_title" maxlength="70"
                                   value="<?= e($post->meta_title) ?>"
                                   class="w-full px-3 py-2 border rounded-lg text-sm"
                                   placeholder="Leave empty to use post title">
                            <div class="text-xs text-neutral-400 mt-1"><span id="metaTitleCount"><?= strlen($post->meta_title ?? '') ?></span>/70</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Meta Description</label>
                            <textarea name="meta_description" rows="2" maxlength="160"
                                      class="w-full px-3 py-2 border rounded-lg text-sm"
                                      placeholder="Leave empty to use excerpt"><?= e($post->meta_description) ?></textarea>
                            <div class="text-xs text-neutral-400 mt-1"><span id="metaDescCount"><?= strlen($post->meta_description ?? '') ?></span>/160</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Focus Keywords</label>
                            <input type="text" name="meta_keywords"
                                   value="<?= e($post->meta_keywords) ?>"
                                   class="w-full px-3 py-2 border rounded-lg text-sm"
                                   placeholder="keyword1, keyword2">
                        </div>
                    </div>

                    <!-- SEO Preview -->
                    <div class="mt-4 pt-4 border-t">
                        <p class="text-xs font-medium text-neutral-500 mb-2">Search Preview</p>
                        <div class="p-3 bg-neutral-50 rounded-lg">
                            <div class="text-blue-700 text-sm font-medium truncate" id="seoPreviewTitle"><?= e($post->meta_title ?: $post->title) ?></div>
                            <div class="text-green-700 text-xs truncate"><?= config('app.url') ?>/blog/<?= e($post->slug) ?></div>
                            <div class="text-neutral-600 text-xs mt-1 line-clamp-2" id="seoPreviewDesc"><?= e($post->meta_description ?: $post->excerpt) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status change handler
    document.querySelector('select[name="status"]').addEventListener('change', function() {
        document.getElementById('scheduleField').classList.toggle('hidden', this.value !== 'scheduled');
    });

    // Meta counters
    document.querySelector('input[name="meta_title"]').addEventListener('input', function() {
        document.getElementById('metaTitleCount').textContent = this.value.length;
        document.getElementById('seoPreviewTitle').textContent = this.value || document.getElementById('title').value;
    });
    document.querySelector('textarea[name="meta_description"]').addEventListener('input', function() {
        document.getElementById('metaDescCount').textContent = this.value.length;
        document.getElementById('seoPreviewDesc').textContent = this.value || document.querySelector('textarea[name="excerpt"]').value;
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
