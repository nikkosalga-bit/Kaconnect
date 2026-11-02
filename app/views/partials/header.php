<?php $user = auth_user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#315c50">
<meta name="description" content="KaConnect — a friendly space to share ideas and stay connected.">
<title><?= e($title ?? 'KaConnect') ?> | KaConnect</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header"><div class="header-inner">
<a class="brand" href="index.php<?= $user ? '?page=feed' : '?page=login' ?>"><span class="brand-mark">K</span><span>Ka<span class="brand-accent">Connect</span><small>YOUR CAMPUS. YOUR COMMUNITY.</small></span></a>
<?php if ($user): ?>
<nav class="main-nav" aria-label="Main navigation"><a href="index.php?page=feed" class="<?= ($title ?? '') === 'Newsfeed' ? 'active' : '' ?>">Home</a><a href="index.php?page=profile">My Profile</a><a href="index.php?page=profile_edit">Settings</a>
<form method="post" action="index.php?page=logout" class="logout-form"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><button type="submit" class="nav-logout">Log out</button></form></nav>
<?php endif; ?>
</div></header>
<?php if ($message = flash('success')): ?><div class="alert success" role="status"><span>✓</span><?= e($message) ?></div><?php endif; ?>
<?php if ($message = flash('error')): ?><div class="alert error" role="alert"><span>!</span><?= e($message) ?></div><?php endif; ?>
