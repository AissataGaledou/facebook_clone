document.addEventListener('DOMContentLoaded', function() {
    // Handle comment forms
    const commentForms = document.querySelectorAll('.add-comment');
    commentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const postId = this.querySelector('input[name="post_id"]').value;
            const content = this.querySelector('input[name="content"]').value;
            
            if (!content.trim()) {
                return;
            }
            
            // Submit comment via AJAX
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('content', content);
            
            fetch('includes/add_comment.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new comment to DOM
                    const commentsList = document.querySelector(`#comments-section-${postId} .comments-list`);
                    
                    // Create new comment element if comments list exists
                    if (commentsList) {
                        const newComment = document.createElement('div');
                        newComment.className = 'comment';
                        newComment.innerHTML = `
                            <a href="profile.php?id=${data.comment.user_id}">
                                <img src="${data.comment.profile_picture}" alt="Profile">
                            </a>
                            <div class="comment-content">
                                <div class="comment-bubble">
                                    <a href="profile.php?id=${data.comment.user_id}" class="comment-author">${data.comment.name}</a>
                                    <p>${data.comment.content}</p>
                                </div>
                                <div class="comment-actions">
                                    <a href="#" class="comment-like">Like</a>
                                    <a href="#" class="comment-reply">Reply</a>
                                    <span class="comment-time">Just now</span>
                                </div>
                            </div>
                        `;
                        
                        commentsList.appendChild(newComment);
                    } else {
                        // Create comments list if it doesn't exist
                        const commentsSection = document.querySelector(`#comments-section-${postId}`);
                        const newCommentsList = document.createElement('div');
                        newCommentsList.className = 'comments-list';
                        newCommentsList.innerHTML = `
                            <div class="comment">
                                <a href="profile.php?id=${data.comment.user_id}">
                                    <img src="${data.comment.profile_picture}" alt="Profile">
                                </a>
                                <div class="comment-content">
                                    <div class="comment-bubble">
                                        <a href="profile.php?id=${data.comment.user_id}" class="comment-author">${data.comment.name}</a>
                                        <p>${data.comment.content}</p>
                                    </div>
                                    <div class="comment-actions">
                                        <a href="#" class="comment-like">Like</a>
                                        <a href="#" class="comment-reply">Reply</a>
                                        <span class="comment-time">Just now</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        commentsSection.insertBefore(newCommentsList, commentsSection.querySelector('.add-comment'));
                    }
                    
                    // Clear input
                    this.querySelector('input[name="content"]').value = '';
                    
                    // Update comment count
                    const commentCountElement = document.querySelector(`#post-${postId} .comment-count`);
                    if (commentCountElement) {
                        const currentCount = parseInt(commentCountElement.textContent) || 0;
                        commentCountElement.textContent = currentCount + 1;
                    }
                } else {
                    console.error('Error adding comment:', data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting comment:', error);
            });
        });
    });

    // Handle post likes
    const likeButtons = document.querySelectorAll('.post-like');
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const postId = this.dataset.postId;
            
            // Submit like via AJAX
            const formData = new FormData();
            formData.append('post_id', postId);
            
            fetch('includes/toggle_like.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle like button appearance
                    this.classList.toggle('liked');
                    
                    // Update like count
                    const likeCountElement = document.querySelector(`#post-${postId} .like-count`);
                    if (likeCountElement) {
                        likeCountElement.textContent = data.likeCount;
                    }
                    
                    // Update button text
                    this.querySelector('span').textContent = this.classList.contains('liked') ? 'Liked' : 'Like';
                } else {
                    console.error('Error toggling like:', data.message);
                }
            })
            .catch(error => {
                console.error('Error liking post:', error);
            });
        });
    });

    // Handle comment likes
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('comment-like') || e.target.parentElement.classList.contains('comment-like')) {
            e.preventDefault();
            
            const commentLike = e.target.classList.contains('comment-like') ? e.target : e.target.parentElement;
            const commentId = commentLike.dataset.commentId;
            
            // Submit comment like via AJAX
            const formData = new FormData();
            formData.append('comment_id', commentId);
            
            fetch('includes/toggle_comment_like.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle like button appearance
                    commentLike.classList.toggle('liked');
                    
                    // Update button text
                    commentLike.textContent = commentLike.classList.contains('liked') ? 'Liked' : 'Like';
                } else {
                    console.error('Error toggling comment like:', data.message);
                }
            })
            .catch(error => {
                console.error('Error liking comment:', error);
            });
        }
    });

    // Handle post sharing
    const shareButtons = document.querySelectorAll('.post-share');
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const postId = this.dataset.postId;
            
            // Create share modal
            const shareModal = document.createElement('div');
            shareModal.className = 'share-modal';
            shareModal.innerHTML = `
                <div class="share-content">
                    <h3>Share this post</h3>
                    <div class="share-options">
                        <button class="share-now">Share now</button>
                        <button class="share-with-text">Write post</button>
                        <button class="share-message">Send as Message</button>
                    </div>
                    <button class="close-share">Cancel</button>
                </div>
            `;
            
            document.body.appendChild(shareModal);
            
            // Add event listeners to share buttons
            shareModal.querySelector('.share-now').addEventListener('click', function() {
                sharePost(postId);
                document.body.removeChild(shareModal);
            });
            
            shareModal.querySelector('.share-with-text').addEventListener('click', function() {
                sharePostWithText(postId);
                document.body.removeChild(shareModal);
            });
            
            shareModal.querySelector('.share-message').addEventListener('click', function() {
                sharePostAsMessage(postId);
                document.body.removeChild(shareModal);
            });
            
            shareModal.querySelector('.close-share').addEventListener('click', function() {
                document.body.removeChild(shareModal);
            });
        });
    });

    // Handle post creation
    const createPostForm = document.querySelector('#create-post-form');
    if (createPostForm) {
        createPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const content = this.querySelector('textarea[name="content"]').value;
            const privacy = this.querySelector('select[name="privacy"]').value;
            
            if (!content.trim()) {
                return;
            }
            
            // Submit post via AJAX
            const formData = new FormData(this);
            
            fetch('includes/create_post.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new post to DOM
                    const newsFeed = document.querySelector('.news-feed');
                    
                    if (newsFeed) {
                        const newPost = document.createElement('div');
                        newPost.className = 'post';
                        newPost.id = `post-${data.post.id}`;
                        newPost.innerHTML = `
                            <div class="post-header">
                                <a href="profile.php?id=${data.post.user_id}">
                                    <img src="${data.post.profile_picture}" alt="Profile">
                                </a>
                                <div class="post-info">
                                    <a href="profile.php?id=${data.post.user_id}" class="post-author">${data.post.name}</a>
                                    <span class="post-time">Just now</span>
                                    <span class="post-privacy">${data.post.privacy}</span>
                                </div>
                                <div class="post-options">
                                    <button class="options-toggle">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="post-content">
                                <p>${data.post.content}</p>
                            </div>
                            <div class="post-stats">
                                <span class="like-count">0</span> likes
                                <span class="comment-count">0</span> comments
                            </div>
                            <div class="post-actions">
                                <button class="post-like" data-post-id="${data.post.id}">
                                    <i class="far fa-thumbs-up"></i>
                                    <span>Like</span>
                                </button>
                                <button class="post-comment" data-post-id="${data.post.id}">
                                    <i class="far fa-comment"></i>
                                    <span>Comment</span>
                                </button>
                                <button class="post-share" data-post-id="${data.post.id}">
                                    <i class="far fa-share-square"></i>
                                    <span>Share</span>
                                </button>
                            </div>
                            <div id="comments-section-${data.post.id}" class="comments-section">
                                <form class="add-comment">
                                    <input type="hidden" name="post_id" value="${data.post.id}">
                                    <input type="text" name="content" placeholder="Write a comment...">
                                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                                </form>
                            </div>
                        `;
                        
                        // Add to top of feed
                        newsFeed.insertBefore(newPost, newsFeed.firstChild);
                        
                        // Clear post form
                        this.reset();
                        
                        // Add event listeners to new post elements
                        attachPostEventListeners(newPost);
                    }
                } else {
                    console.error('Error creating post:', data.message);
                }
            })
            .catch(error => {
                console.error('Error submitting post:', error);
            });
        });
    }

    // Add image upload preview for posts
    const imageUpload = document.querySelector('#post-image');
    const previewContainer = document.querySelector('.image-preview');
    
    if (imageUpload && previewContainer) {
        imageUpload.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            
            if (this.files.length > 0) {
                for (let i = 0; i < this.files.length; i++) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'preview-image';
                        preview.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button class="remove-image" data-index="${i}">×</button>
                        `;
                        
                        previewContainer.appendChild(preview);
                    };
                    
                    reader.readAsDataURL(this.files[i]);
                }
                
                previewContainer.style.display = 'flex';
            } else {
                previewContainer.style.display = 'none';
            }
        });
        
        // Handle image removal
        previewContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-image')) {
                const index = e.target.dataset.index;
                // Need to clear the file input, this is tricky with multiple files
                // For simplicity, just clear all and hide preview
                imageUpload.value = '';
                previewContainer.innerHTML = '';
                previewContainer.style.display = 'none';
            }
        });
    }

    // Helper functions for sharing posts
    function sharePost(postId) {
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('share_type', 'share');
        
        fetch('includes/share_post.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Post shared successfully!');
            } else {
                console.error('Error sharing post:', data.message);
            }
        })
        .catch(error => {
            console.error('Error sharing post:', error);
        });
    }
    
    function sharePostWithText(postId) {
        // Open a modal to add text to shared post
        const shareTextModal = document.createElement('div');
        shareTextModal.className = 'share-text-modal';
        shareTextModal.innerHTML = `
            <div class="share-text-content">
                <h3>Share with your thoughts</h3>
                <textarea placeholder="What's on your mind?"></textarea>
                <div class="share-buttons">
                    <button class="cancel-share-text">Cancel</button>
                    <button class="submit-share-text">Share</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(shareTextModal);
        
        shareTextModal.querySelector('.cancel-share-text').addEventListener('click', function() {
            document.body.removeChild(shareTextModal);
        });
        
        shareTextModal.querySelector('.submit-share-text').addEventListener('click', function() {
            const text = shareTextModal.querySelector('textarea').value;
            
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('share_type', 'share_with_text');
            formData.append('content', text);
            
            fetch('includes/share_post.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.body.removeChild(shareTextModal);
                    showNotification('Post shared with your thoughts!');
                } else {
                    console.error('Error sharing post:', data.message);
                }
            })
            .catch(error => {
                console.error('Error sharing post:', error);
            });
        });
    }
    
    function sharePostAsMessage(postId) {
        // Open a modal to select friends
        const friendsModal = document.createElement('div');
        friendsModal.className = 'friends-modal';
        friendsModal.innerHTML = `
            <div class="friends-content">
                <h3>Send to friends</h3>
                <div class="search-friends">
                    <input type="text" placeholder="Search friends...">
                </div>
                <div class="friends-list">
                    <p>Loading friends...</p>
                </div>
                <div class="friends-buttons">
                    <button class="cancel-friends">Cancel</button>
                    <button class="send-to-friends" disabled>Send</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(friendsModal);
        
        // Load friends list
        fetch('includes/get_friends.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const friendsList = friendsModal.querySelector('.friends-list');
            friendsList.innerHTML = '';
            
            if (data.friends && data.friends.length > 0) {
                data.friends.forEach(friend => {
                    const friendElement = document.createElement('div');
                    friendElement.className = 'friend-item';
                    friendElement.dataset.id = friend.id;
                    friendElement.innerHTML = `
                        <img src="${friend.profile_picture}" alt="${friend.name}">
                        <span>${friend.name}</span>
                        <input type="checkbox" name="selected_friend">
                    `;
                    
                    friendsList.appendChild(friendElement);
                });
                
                // Enable select functionality
                const friendItems = friendsList.querySelectorAll('.friend-item');
                const sendButton = friendsModal.querySelector('.send-to-friends');
                
                friendItems.forEach(item => {
                    item.addEventListener('click', function() {
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        checkbox.checked = !checkbox.checked;
                        
                        // Enable send button if at least one friend is selected
                        const selectedFriends = friendsList.querySelectorAll('input[type="checkbox"]:checked');
                        sendButton.disabled = selectedFriends.length === 0;
                    });
                });
                
                // Search functionality
                const searchInput = friendsModal.querySelector('.search-friends input');
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    friendItems.forEach(item => {
                        const name = item.querySelector('span').textContent.toLowerCase();
                        if (name.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            } else {
                friendsList.innerHTML = '<p>No friends found.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading friends:', error);
            friendsModal.querySelector('.friends-list').innerHTML = '<p>Error loading friends.</p>';
        });
        
        // Handle cancel
        friendsModal.querySelector('.cancel-friends').addEventListener('click', function() {
            document.body.removeChild(friendsModal);
        });
        
        // Handle send
        friendsModal.querySelector('.send-to-friends').addEventListener('click', function() {
            const selectedFriends = Array.from(friendsModal.querySelectorAll('input[type="checkbox"]:checked'))
                .map(checkbox => checkbox.closest('.friend-item').dataset.id);
            
            if (selectedFriends.length > 0) {
                const formData = new FormData();
                formData.append('post_id', postId);
                formData.append('share_type', 'message');
                formData.append('friends', JSON.stringify(selectedFriends));
                
                fetch('includes/share_post.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.body.removeChild(friendsModal);
                        showNotification(`Post sent to ${selectedFriends.length} friends!`);
                    } else {
                        console.error('Error sharing post:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error sharing post:', error);
                });
            }
        });
    }

    // Helper function to attach event listeners to new post elements
    function attachPostEventListeners(postElement) {
        // Like button
        const likeButton = postElement.querySelector('.post-like');
        if (likeButton) {
            likeButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                const postId = this.dataset.postId;
                
                // Submit like via AJAX
                const formData = new FormData();
                formData.append('post_id', postId);
                
                fetch('includes/toggle_like.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Toggle like button appearance
                        this.classList.toggle('liked');
                        
                        // Update like count
                        const likeCountElement = postElement.querySelector('.like-count');
                        if (likeCountElement) {
                            likeCountElement.textContent = data.likeCount;
                        }
                        
                        // Update button text
                        this.querySelector('span').textContent = this.classList.contains('liked') ? 'Liked' : 'Like';
                    } else {
                        console.error('Error toggling like:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error liking post:', error);
                });
            });
        }
        
        // Share button
        const shareButton = postElement.querySelector('.post-share');
        if (shareButton) {
            shareButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                const postId = this.dataset.postId;
                
                // Create share modal
                const shareModal = document.createElement('div');
                shareModal.className = 'share-modal';
                shareModal.innerHTML = `
                    <div class="share-content">
                        <h3>Share this post</h3>
                        <div class="share-options">
                            <button class="share-now">Share now</button>
                            <button class="share-with-text">Write post</button>
                            <button class="share-message">Send as Message</button>
                        </div>
                        <button class="close-share">Cancel</button>
                    </div>
                `;
                
                document.body.appendChild(shareModal);
                
                // Add event listeners to share buttons
                shareModal.querySelector('.share-now').addEventListener('click', function() {
                    sharePost(postId);
                    document.body.removeChild(shareModal);
                });
                
                shareModal.querySelector('.share-with-text').addEventListener('click', function() {
                    sharePostWithText(postId);
                    document.body.removeChild(shareModal);
                });
                
                shareModal.querySelector('.share-message').addEventListener('click', function() {
                    sharePostAsMessage(postId);
                    document.body.removeChild(shareModal);
                });
                
                shareModal.querySelector('.close-share').addEventListener('click', function() {
                    document.body.removeChild(shareModal);
                });
            });
        }
        
        // Comment form
        const commentForm = postElement.querySelector('.add-comment');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const postId = this.querySelector('input[name="post_id"]').value;
                const content = this.querySelector('input[name="content"]').value;
                
                if (!content.trim()) {
                    return;
                }
                
                // Submit comment via AJAX
                const formData = new FormData();
                formData.append('post_id', postId);
                formData.append('content', content);
                
                fetch('includes/add_comment.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Handle the comment addition as in the original code
                        // ...
                        
                        // Clear input
                        this.querySelector('input[name="content"]').value = '';
                    } else {
                        console.error('Error adding comment:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error submitting comment:', error);
                });
            });
        }
    }

    // Show notification helper
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Show animation
        setTimeout(() => {
            notification.classList.add('visible');
        }, 10);
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.remove('visible');
            
            // Remove from DOM after animation
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});