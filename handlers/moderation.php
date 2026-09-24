<?php

// Reporting content, and the moderation queue.

function show_moderator() {
    $success = flash_get('success');
    $error = flash_get('error');
    if (can('moderate_posts') || can('moderate_comments')) {
        $reports = list_reports();
        require __DIR__ . '/../views/moderator.php';
    } else {
        deny();
    }
}

function post_report_create() {
    require_verified();

    $targetType = $_POST['target_type'] ?? '';
    $targetId = $_POST['id'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (!in_array($targetType, ['post', 'comment'])) {
        flash_set('error', 'Invalid target type');
        redirect(local_path($_POST['back'] ?? '', '/posts/show?id=' . ($targetType === 'post' ? $targetId : find_comment($targetId)['post_id'])));
    }

    $target = $targetType === 'post' ? find_post($targetId) : find_comment($targetId);

    if (!$target) {
        http_response_code(404);
        echo '404 Not Found';
        exit;
    }

    $user = current_user();

    if ($target['user_id'] == $user['id']) {
        flash_set('error', 'You can not report your own post or comment');
        redirect(local_path($_POST['back'] ?? '', '/posts/show?id=' . ($targetType === 'post' ? $targetId : find_comment($targetId)['post_id'])));
    }

    if (!$reason) {
        flash_set('error', 'Reason is required');
        redirect(local_path($_POST['back'] ?? '', '/posts/show?id=' . ($targetType === 'post' ? $targetId : find_comment($targetId)['post_id'])));
    } else if (strlen($reason) > 255) {
        flash_set('error', 'Reason must be less than 255 characters long');
        redirect(local_path($_POST['back'] ?? '', '/posts/show?id=' . ($targetType === 'post' ? $targetId : find_comment($targetId)['post_id'])));
    }

    try {
        $reportedId = create_report(
            $user['id'],
            $targetType,
            $targetId,
            $reason
        );

        flash_set('success', 'Report submitted successfully');
        redirect(local_path($_POST['back'] ?? '', '/posts/show?id=' . ($targetType === 'post' ? $targetId : find_comment($targetId)['post_id'])));
    } catch (Exception $e) {
        if ($e->getCode() === '23000') {
            flash_set('error', 'You have already reported this post');
        } else {
            flash_set('error', 'Failed to report post');
        }
        redirect(local_path($_POST['back'] ?? '', '/posts/show?id=' . ($targetType === 'post' ? $targetId : find_comment($targetId)['post_id'])));
    }
}

function post_moderation_hide_post() {
    if (can('moderate_posts')) {
        delete_post($_POST['id']);
        log_activity('post.deleted_by_moderator', 'post', $_POST['id']);
        flash_set('success', 'Post hidden successfully');
        redirect('/moderator');
    } else {
        deny();
    }
}

function post_moderation_hide_comment() {
    if (can('moderate_comments')) {
        delete_comment($_POST['id']);
        log_activity('comment.deleted_by_moderator', 'comment', $_POST['id']);
        flash_set('success', 'Comment hidden successfully');
        redirect('/moderator');
    } else {
        deny();
    }
}
