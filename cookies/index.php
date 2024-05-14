<?php
    $cookieJson = '';
    $cookieData = array();
    if (isset($_COOKIE['CWC-Profile'])) {
        $cookieJson = $_COOKIE['CWC-Profile'];
        $cookieData = json_decode($cookieJson, true);
    } else {
        $cookieJson = '没有 CWC-Profile Cookie 数据';
    }
    
    //Google Analytics
    $analytics = false;
    if (isset($_COOKIE['CWC-Profile'])) {
        $cookieData = json_decode($_COOKIE['CWC-Profile'], true);
        if (!empty($cookieData["Google Analytics"]) && $cookieData["Google Analytics"] === "true") {
            $analytics = true;
        }
    }
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>管理 CWC 所使用的 Cookies - CWC</title>
    <link rel="stylesheet" href="https://resource.caner.hk/get/misans/cwc_get.css">
    <link rel="stylesheet" href="https://resource.caner.hk/get/toggle/get.css">
    <link rel="icon" href="https://resource.caner.hk/get/logo/cwc.png" type="image/x-icon">
    <?php if ($analytics): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-BMQH2HN7KX"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-BMQH2HN7KX');
    </script>
    <?php endif; ?>
    <style>
    body {
        padding-top: 58px;
        font-family: 'misans', sans-serif;
    }
    html {
        scroll-behavior: smooth;
    }
    html ,body {
        margin: 0;
    }
    ::selection {
        background-color: rgba(0, 0, 0, 0.8);
        color: #FFF;
    }
    @media (min-width: 600px) {
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px; 
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555; 
        }
    }
    #backtopBtn {
        display: none;
    }
    #gameFrame {
        border: 1px solid #ccc;
        transition: opacity 0.5s ease, max-height 0.5s ease;
        overflow: hidden;
    }
    #button-text {
        transition: opacity 0.3s ease;
        opacity: 1;
    }
    #changeTitle span, #changeChart span {
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }
    header {
        background-color: #FAF9F8;
        padding-left: 10px;
        position: fixed;
        top: 0;
        width: 100%;
        height: 58px;
        box-shadow: none;
        z-index: 100;
        transition: box-shadow 0.3s ease;
    }
    footer {
        background-color: #FAF9F8;
    }
    a {
        text-decoration: none;
    }
    @keyframes blink-border {
        0%, 100% { 
            border-color: #F44336; 
            background-color: rgba(239, 83, 80, 0.5);
        }
        50% { 
            border-color: transparent; 
            background-color: rgba(0, 0, 0, 0);
        }
    }
    @media screen and (max-width: 600px) {
        .hide-xs {
            display: none;
        }
    }
    @media screen and (max-width: 400px) {
        .hide-s {
            display: none;
        }
    }
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes shake {
        0% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        50% { transform: translateX(5px); }
        75% { transform: translateX(-5px); }
        100% { transform: translateX(0); }
    }
    #suggestions {
        position: absolute;
        border: 1px solid #000;
        list-style-type: none;
        padding: 0;
        margin-top: 5px;
        width: 250px;
        background-color: white;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 99;
        box-sizing: border-box;
        transition: all 0.3s ease;
        transform-origin: top;
        transform: scaleY(0);
        max-height: 220px;
        overflow-y: auto;
    }
    #suggestions li {
        padding: 8px;
        cursor: pointer;
        transition: color 0.3s, background-color 0.3s ease, color 0.3s ease;
        border-bottom: 1px dashed #ccc;
        box-sizing: border-box;
    }
    #suggestions li:last-child {
        border-bottom: none;
    }
    #suggestions li:hover {
        background-color: #000;
        color: #FFF;
    }   
    .fade-text {
        opacity: 1;
        transition: opacity 0.3s ease;
    }
    @keyframes fadeInOut {
        0%, 100% { opacity: 0; }
        50% { opacity: 1; }
    }
    .fade-in-out {
        animation: fadeInOut 0.3s ease-in-out;
    }
    .text-fade-out {
        animation: fadeOut 0.3s forwards;
    }
    .text-fade-in {
        animation: fadeIn 0.3s forwards;
    }
    .hidden {
        opacity: 0;
    }
    .header-btn {
        background-color: transparent;
        border: none;
        cursor: pointer;
        transition: transform 0.5s;
        transform-origin: 50% 43%;
    }
    .cwc-menu {
        width: 100%;
        height: 410px;
        background-color: #FAF9F8;
        position: relative;
        z-index: 99;
        margin-top: -409px;
        transition: margin-top 0.5s ease;
        overflow: hidden;
        border-bottom: 1px solid black;
    }
    .cwc-menu-content {
        padding-left: 10px;
        padding-right: 10px;
    }
    .cwc-icon-btn, .cwc-input {
        box-sizing: border-box;
        height: 42px; 
        border: 1px solid #000;
        background-color: transparent;
        border-radius: 0px !important;
    }
    .cwc-icon-btn {
        width: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 5px;
        transition: color 0.3s, background-color 0.3s, border-color 0.3s;
    }
    .cwc-icon-btn svg {
        fill: #000;
        transition: fill 0.3s;
    }
    .cwc-icon-btn:hover {
        background-color: #000;
    }
    .cwc-icon-btn:hover svg {
        fill: white;
    }
    .cwc-icon-btn svg polyline,
    .cwc-icon-btn svg line {
        stroke: #000;
    }
    .cwc-icon-btn:hover svg polyline,
    .cwc-icon-btn:hover svg line {
        stroke: white;
    }
    .cwc-margin-10 {
        width: calc(100% - 20px);
        margin-left: 10px;
        margin-right: 10px;
    }
    .cwc-input {
        width: 250px;
        padding-left: 10px;
    }
    .cwc-input:focus {
        outline: none;
    }
    .cwc-game {
        margin-top: 10px;
        margin-bottom: 10px;
        width: 100%;
        opacity: 0;
        max-height: 0;
    }
    .cwc-btn {
        border-radius: 0px !important;
        border: 1px solid black;
        height: 36px;
        width: 297px;
        font-size: 14px;
        transition: color 0.3s, background-color 0.3s, border-color 0.3s;
        background-color: transparent;
        color: black;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        margin-bottom: 5px;
    }
    .cwc-btn-delete {
        border-radius: 0px !important;
        border: 1px solid #EF5350;
        height: 36px;
        width: 297px;
        font-size: 14px;
        transition: color 0.3s, background-color 0.3s, border-color 0.3s;
        background-color: transparent;
        color: #EF5350;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        margin-bottom: 5px;
    }
    .cwc-btn:hover {
        background-color: black;
        color: white;
    }
    .cwc-btn-delete:hover {
        background-color: #EF5350;
        color: white;
    }
    .cwc-action-btn {
        margin-left: 0px;
        margin-bottom: 0px;
        margin-top: 10px;
    }
    .cwc-link {
        color: #1976D2;
        border-bottom: 1px solid #1976D2;
        text-decoration: none; 
        white-space: nowrap; 
        transition: border-bottom 0.3s ease;
    }
    .cwc-link:visited {
        color: #1976D2;
    }
    .cwc-link:after {
        content: '';
        display: inline-block;
        width: 18px;
        height: 18px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><polygon points="7 7 15.586 7 5.293 17.293 6.707 18.707 17 8.414 17 17 19 17 19 5 7 5 7 7" fill="%231976D2"/></svg>');
        background-size: contain;
        margin-left: -2px;
        margin-top: -2px;
        vertical-align: middle;
        white-space: nowrap; 
        transition: transform 0.3s ease;
    }
    .cwc-footer-link {
        color: black;
        font-size: 14px;
        border-bottom: 1px solid;
        white-space: nowrap;
        transition: border-bottom 0.3s ease;
    }
    .cwc-footer-link:visited {
        color: black;
    }
    .cwc-footer-link:after {
        content: '';
        display: inline-block;
        width: 18px;
        height: 18px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><polygon points="7 7 15.586 7 5.293 17.293 6.707 18.707 17 8.414 17 17 19 17 19 5 7 5 7 7" fill="%23000000"/></svg>');
        background-size: contain;
        margin-left: -2px;
        margin-top: -2px;
        vertical-align: middle;
        white-space: nowrap; 
        transition: transform 0.3s ease;
    }
    .cwc-footer-link-noafter {
        color: black;
        font-size: 14px;
        border-bottom: 1px solid;
        white-space: nowrap;
        transition: border-bottom 0.3s ease;
    }
    .cwc-footer-link-noafter:visited {
        color: black;
    }
    .cwc-footer-top {
        color: black;
        font-size: 14px;
        border-bottom: 1px solid;
        white-space: nowrap;
    }
    .cwc-footer-top:visited {
        color: black;
    }
    .cwc-footer-top:hover,
    .cwc-footer-link:hover,
    .cwc-link:hover,
    .cwc-footer-link-noafter:hover {
        border-bottom: 2px solid;
    }
    .cwc-link:hover:after,
    .cwc-footer-link:hover:after {
        transform: rotate(45deg);
    }
    .link-space {
        margin-right: 15px;
    }
    .initial-state {
        display: block;
        opacity: 0;
    }
    .shadow {
        box-shadow: 2px 4px 2px -2px gray;
    }
    .footer {
        padding: 10px;
        border-top: 1px solid #2a2a2a;
    }
    .footer-border {
        border-color: #2a2a2a !important;
    }
    .cwc-flex {
        display: flex;
    }
    .cwc-svg-right {
        float: right !important;
    }
    .fade-out {
        animation: fadeOut 0.25s;
        opacity: 0;
    }
    .fade-in {
        animation: fadeIn 0.25s;
        animation-fill-mode: forwards;
    }
    .cwc-wraptextr {
        overflow-wrap: break-word;
        word-wrap: break-word;
    }
    .cwc-close-btn {
        border: none;
        background: none;
        cursor: pointer;
        padding: 0;
        margin-left: -82px;
        height: 24px;
        display: none;
    }
    .shake {
        animation: shake 0.5s;
    }
    .cwc-menu-btn {
        position: fixed;
        right: 10px;
        top: 14px;
    }
    .cwc-title {
        position: relative;
        top: -4px;
    }
    .cwc-headline {
        font-size: 52px;
    }
    .cwc-subhead {
         font-size: 22px;
    }
    .cwc-subhead-b {
         font-size: 19px;
    }
    .cwc-headtip-n {
        float: right;
        position: relative;
        top: 14px;
        right: 48px;
        font-size: 10px;
    }
    .cwc-text-small {
        font-size: 14px;
    }
    .cwc-text-normal {
        font-size: 16px;
    }
    .cwc-hr {
        border: 0;
        border-top: 
        1px solid #ccc;
        margin-top: 30px;
        margin-bottom: 30px;
    }
    .cwc-errorpage-hr {
        border: 0;
        border-top: 
        1px solid #ccc;
        margin-top: 15px;
        margin-bottom: 15px;
    }
    .cwc-top-20 {
        margin-top: 20px;
    }
    .cwc-hr-dashed {
        border: 0;
        border-top: 1px dashed #ccc;
        margin-top: 30px;
        margin-bottom: 15px;
    }
    .cwc-hr-double {
        border: 0;
        border-top: 3px double #ccc;
        margin-top: 30px;
        margin-bottom: 15px;
    }
    .cwc-hr-solid {
        border: 0;
        border-top: 1px solid #ccc;
        margin-top: 30px;
        margin-bottom: 15px;
    }
    .cwc-menu-label {
        font-weight: bold;
        font-size: 16px;
        margin-bottom: 5px;
    }
    .cwc-menu-interval {
        margin-top: 12px;
    }
    .cwc-horizontal-container {
        display: flex;
        align-items: center;
    }
    .cwc-maps-label, .cwc-maps-span, .cwc-maps-link {
        font-family: 'canerfont';
        font-size: 12px;
    }
    .cwc-x {
        position: relative;
        bottom: 2px;
    }
    .cwc-typo {
        margin-top: 20px;
    }
    .cwc-typo blockquote {
        margin:1em 0;
        padding-left:1em;
        font-weight:400;
        border-left:4px solid #ccc;
    }
    .cwc-typo blockquote:last-child {
        margin-bottom:0
    }
    .cwc-typo blockquote footer {
        color:rgba(0,0,0,.54);
        font-size:86%
    }
    .cwc-break-word {
      overflow-wrap: break-word;
    }
    .footer-head {
        font-size: 25px;
    }
    .error-page-btn {
        margin-top: 8px;
    }
    .follow-icon {
      font-size: 36px;
      margin: 0px 4px 0px 4px;
    }
    .follow-icon-container {
        padding-bottom: 15px;
    }
    .cwc-svg path,
    .cwc-svg rect {
        fill: #2a2a2a !important;
    }
    .c-w-c {
        color: #2a2a2a !important;
    }
    .cwc-page-container {
        padding: 0px 15px 30px 15px;
    }
    .cwc-big-title {
        font-size: 28px;
    }
    .cwc-big-subtitle {
        position: relative;
        bottom: 20px;
        font-size: 14px;
        opacity: 0.8;
    }
    .click-kaomoji {
        position: relative;
        bottom: 3px;
    }
    .cwc-flex-center-container {
        display: flex;
        align-items: center;
    }
    .cwc-m-fix {
        margin-top: -10px;
    }
    .cwc-m2-fix {
        margin-top: -15px;
    }
    .cwc-progress-determinate, .progress-arrow, .arrow-text {
        transition: width 1s ease, left 1s ease;
    }
    ul {
        padding-left: 30px;
    }
    .radio-btn-container {
        margin-bottom: 10px;
    }
    .cwc-btn-delete:disabled,
    .cwc-btn-delete:disabled .button-text {
        border: 1px solid #cccccc;
        background-color: #cccccc;
        color: white;
        cursor: not-allowed;
    }
    .cwc-btn-delete {
        transition: background-color 0.25s ease, border 0.25s ease, color 0.25s ease;
    }
    #delete-tip {
        transition: color 0.25s ease;
    }
    .tip-fade-out {
        animation: fadeOut 0.3s forwards;
    }
    .tip-fade-in {
        animation: fadeIn 0.3s forwards;
    }
    .cwc-radio {
        position: relative;
        display: inline-block;
        height: 36px;
        padding-left: 28px;
        line-height: 36px;
        font-weight: bold;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    .cwc-radio input {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
        opacity: 0;
    }
    .cwc-radio-icon {
        position: absolute;
        top: 9px;
        left: 0;
        display: inline-block;
        -webkit-box-sizing: border-box;
            box-sizing: border-box;
        width: 18px;
        height: 18px;
        vertical-align: middle;
        border: 2px solid rgba(0, 0, 0, 0.65);
        border-radius: 18px;
        -webkit-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), -webkit-box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), -webkit-box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1), -webkit-box-shadow 0.14s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cwc-radio-icon::before {
        position: absolute;
        top: 0px;
        left: 0;
        width: 14px;
        height: 14px;
        background-color: #1976D2;
        border-radius: 14px;
        -webkit-transform: scale(0);
        transform: scale(0);
        opacity: 0;
        -webkit-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        content: ' ';
    }
    .cwc-radio input[type="radio"]:checked + .cwc-radio-icon {
        border-color: #1976D2;
    }
    .cwc-radio input[type="radio"]:checked + .cwc-radio-icon::before {
        -webkit-transform: scale(0.68);
              transform: scale(0.68);
        opacity: 1;
    }
    .cookie-container {
        margin-bottom: 15px;
    }
    .cookie-json {
        background-color: #f5f5f5;
        padding: 10px;
        margin-top: 22px;
        border: 1px solid #ccc;
        margin-bottom: 10px;
        word-wrap: break-word;
        word-break: break-all;
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden; 
    }
    .cwc-scrollable-container {
        background-color: #f5f5f5;
        max-height: 350px;
        overflow-y: auto;
        overflow-x: auto; 
        border: 1px solid #ccc;
        margin-top: 22px;
        white-space: nowrap;
    }
    .cwc-checkbox {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        top: 2px;
        left: -4px;
        border: 1px solid black;
        border-radius: 0px !important;
        cursor: pointer;
        display: inline-block;
        position: relative;
        background-repeat: no-repeat;
        background-position: center;
        background-size: 80%;
        transition: background-color 0.2s, background-image 0.2s;
        z-index: 98;
    }
    .cwc-checkbox-label {
        position: relative;
        font-size: 15px;
        top: 0px;
    }
    .cwc-checkbox:checked {
        background-color: black;
        border-color: black;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23FFFFFF" viewBox="0 0 24 24"><path d="M 20.292969 5.2929688 L 9 16.585938 L 4.7070312 12.292969 L 3.2929688 13.707031 L 9 19.414062 L 21.707031 6.7070312 L 20.292969 5.2929688 z"/></svg>');
    }
    .cwc-acc-style:focus {
        outline: 3px solid #FFC400 !important;
    }
    .switch-label {
        margin-right: 10px;
    }
    @media (prefers-color-scheme: dark) {
        ::selection {
            background-color: #FFF;
            color: rgba(0, 0, 0, 0.8);
        }
        #suggestions {
            background-color: #121212;
            color: #FFF;
            box-shadow: 0px 2px 5px rgba(255,255,255,0.5);
        }
        #suggestions {
            border: 1px solid #FFF;
        }
        #suggestions li:hover {
            background-color: #FFF;
            color: #000;
        }   
        header {
            background-color: #121212;
        }
        footer {
            background-color: #121212;
        }
        .cookie-json {
            background-color: #363636;
        }
        .c-w-c {
            color: #FFF !important;
        }
        .cwc-footer-link {
          color: white;
        }
        .cwc-footer-link:visited {
          color: white;
        }
        .cwc-footer-link:after {
          background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><polygon points="7 7 15.586 7 5.293 17.293 6.707 18.707 17 8.414 17 17 19 17 19 5 7 5 7 7" fill="%23FFFFFF"/></svg>');
        }
        .footer-border {
            border-color: white !important;
        }
        .cwc-footer-top {
          color: white;
        }
        .cwc-footer-top:visited {
          color: white;
        }
        .cwc-btn {
          border-color: white;
          background-color: transparent;
          color: white;
        }
        .cwc-btn:hover {
          background-color: white;
          color: black;
        }
        .shadow {
            box-shadow: 2px 4px 2px -2px rgba(255, 255, 255, 0.5);
        }
        .cwc-menu {
            background-color: #121212 !important;
            border-color: white !important;
        }
        body {
            background-color: #212121;
            color: #FFF;
            transition: color 0.3s ease;
        }
        .cwc-input {
            border-color: white;
        }
        .cwc-input {
            border-color: white;
            color: white;
        }
        .cwc-icon-btn {
            border-color: white;
        }
        .cwc-icon-btn svg {
            fill: white;
        }
        .cwc-icon-btn:hover {
            background-color: white;
        }
        .cwc-icon-btn:hover svg {
            fill: black;
        }
        .cwc-icon-btn svg polyline,
        .cwc-icon-btn svg line {
            stroke: white;
        }
        .cwc-icon-btn:hover svg polyline,
        .cwc-icon-btn:hover svg line {
            stroke: black;
        }
        .footer {
            border-top: 1px solid white !important;
        }
        .cwc-svg path,
        .cwc-svg rect {
            fill: white !important;
        }
        .menu-svg polyline {
            stroke: white !important;
        }
        .totop-btn polyline, .totop-btn line {
            stroke: white !important;
        }
        .cwc-radio-icon {
            border-color: rgba(256, 256, 256, 0.65);
        }
        .cwc-scrollable-container {
          background-color: #363636;
        }
        .cwc-checkbox {
            border-color: white;
            background-color: transparent;
        }
        .cwc-checkbox:checked {
            background-color: white;
            border-color: white;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" fill="%23000000" viewBox="0 0 24 24"><path d="M 20.292969 5.2929688 L 9 16.585938 L 4.7070312 12.292969 L 3.2929688 13.707031 L 9 19.414062 L 21.707031 6.7070312 L 20.292969 5.2929688 z"/></svg>');
        }
    }
    </style>
</head>
<body>
    <header>
        <div class="cwc-svg-right">
            <button id="menuBtn" class="header-btn cwc-acc cwc-menu-btn">
                <svg class="menu-svg" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512"><polyline points="112 184 256 328 400 184" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/></svg>
            </button>
        </div>
    
        <button id="backtopBtn" class="header-btn cwc-acc cwc-menu-btn">
            <svg class="totop-btn" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512"><polyline points="112 244 256 100 400 244" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/><line x1="256" y1="120" x2="256" y2="412" style="fill:none;stroke:#000;stroke-linecap:square;stroke-miterlimit:10;stroke-width:48px"/></svg>
        </button>
    
        <div class="cwc-flex">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 512 512" style="position: relative; top: 14px; margin-right: 6px;" class="cwc-svg cwc-acc" aria-label="Caner Weather Channel 的 LOGO"><path d="M340,480H106c-29.5,0-54.92-7.83-73.53-22.64C11.23,440.44,0,415.35,0,384.8c0-26.66,10.08-49.8,29.14-66.91,15.24-13.68,36.17-23.21,59-26.84h0c.06,0,.08,0,.09-.05,6.44-39,23.83-72.09,50.31-95.68A140.24,140.24,0,0,1,232,160c30.23,0,58.48,9.39,81.71,27.17a142.69,142.69,0,0,1,45.36,60.66c29.41,4.82,54.72,17.11,73.19,35.54C453,304.11,464,331.71,464,363.2c0,32.85-13.13,62.87-37,84.52C404.11,468.54,373.2,480,340,480Zm19-232.18Z"/><path d="M381.5,219.89a169.23,169.23,0,0,1,45.44,19A96,96,0,0,0,281,129.33q-2.85,2-5.54,4.2a162.47,162.47,0,0,1,57.73,28.23A174.53,174.53,0,0,1,381.5,219.89Z"/><rect x="448" y="192" width="64" height="32"/><rect x="320" y="32" width="32" height="64"/><path d="M255.35,129.63l12.45-12.45L223.18,72.55,200.55,95.18l33.17,33.17h.6A172,172,0,0,1,255.35,129.63Z"/><rect x="406.27" y="90.18" width="63.11" height="32" transform="translate(53.16 340.68) rotate(-45)"/></svg> 
            <h2 id="cwc-title" class="cwc-title cwc-acc c-w-c"><a href="https://weather.caner.hk" style="text-decoration: none; color: inherit; visited: inherit;"><span class="hide-s">Caner </span>Weather<span class="hide-xs"> Channel</span></a></h2>
        </div>
    </header>
    
    <nav class="cwc-menu">
        <div class="cwc-menu-content">
        <h3 class=" cwc-acc">搜索天气</h3>
            <form class="cwc-menu-search cwc-m-fix cwc-acc"  aria-label="搜索天气的表单" method="get" action="https://weather.caner.hk/">
                <div class="cwc-flex-center-container">
                    <input class="cwc-input cwc-acc" aria-label="搜索天气的输入框" type="text" name="location" id="location" placeholder="输入地名、邮编或纬度,经度">
                    <button class="cwc-icon-btn cwc-acc" type="submit" id="btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M464,428,339.92,303.9a160.48,160.48,0,0,0,30.72-94.58C370.64,120.37,298.27,48,209.32,48S48,120.37,48,209.32s72.37,161.32,161.32,161.32a160.48,160.48,0,0,0,94.58-30.72L428,464ZM209.32,319.69A110.38,110.38,0,1,1,319.69,209.32,110.5,110.5,0,0,1,209.32,319.69Z"/></svg>
                    </button>
                    <button class="cwc-close-btn cwc-svg cwc-acc" id="closeBtn"><svg xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 512 512"><path d="M400 145.49L366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256 400 145.49z"/></svg>
                    </button>
                </div>
                <ul id="suggestions" class="cwc-acc"></ul>
            </form>
        <h3 class="cwc-acc">热门城市</h3>
        <div class="cwc-m-fix cwc-acc" id="btn-group"></div>
        <h3>辅助功能</h3>
            <div class="cwc-horizontal-container  cwc-m2-fix cwc-acc">
                <label for="acc-checkbox" class="cwc-checkbox-label switch-label cwc-acc">无障碍模式 (使用视觉聚焦和 TTS 朗读)</label>
                <input id="acc-checkbox" type="checkbox" class="switch cwc-acc" name="acc-checkbox" aria-label="无障碍模式的滑动开关">
            </div>
    
        </div>
    </nav>
    
    <main>
    
        <div class="cwc-page-container">
        <h2 class="cwc-big-title cwc-acc">CWC 所使用的 Cookies</h2>
        
        <p class="cwc-acc">Cookie 是在您访问网站时产生并保存在您的手机、平板电脑或计算机上的文件。<br><br>它们用于收集和存储有关您的网站使用情况的信息，比如您访问的页面，以及您与网站的互动方式等。<br><br>在此页面，我们对 CWC 所使用的 Cookies 都提供了其功能的陈述，并且允许您可以管理您的 Cookies 偏好。</p>
        
        <hr class="cwc-hr-solid">
        <strong class="cwc-subhead cwc-acc">Cookies 概览</strong>
        <p class="cwc-acc">Caner Weather Channel 使用两种类型的 Cookies 来存储信息：一种是名为 CWC-Profile 的 Cookie，由 CWC 直接创建和管理；另一种是第三方 Cookies，由我们的服务提供商创建。你可以根据你的意愿选择您想要使用的 Cookie。</p>
        
        <hr class="cwc-hr-solid">
        <strong class="cwc-subhead cwc-acc">用于分析使用数据的 Cookie</strong>
        <p class="cwc-acc">我们使用 <a href="https://developers.google.com/analytics/devguides/platform?hl=zh-cn" class="cwc-link" target="_blank">Google Analytics</a> 来分析您的浏览偏好。这项服务帮助我们了解哪些页面最受欢迎，您是如何发现我们的网站的，以及您在网站上的停留时间。这些信息使我们能够优化网站内容，确保我们提供的服务更贴合您的需求，从而为您提供更优质的浏览体验。<br><br>为做到这些功能，Google Analytics 会在您的设备上存储一些 Cookies 并记录一些信息例如：</p>
        <ul class="cwc-acc"  aria-label="无需列表">
            <li class="cwc-acc">您访问网站的次数，以及访问的时间。</li>
            <li class="cwc-acc">您从哪里或什么方式访问我们的网站。</li>
            <li class="cwc-acc">您浏览了哪些页面以及浏览所花费的时间。</li>
        </ul>
        <p class="cwc-acc">这些信息以匿名存储，这意味着我们无法识别您的个人身份。并且我们不允许 Google 出于自身目的使用或共享这些数据。</p>
        
        <form class="cwc-acc" id="cwc-analytics" aria-label="选择是否使用Google分析的表单">
            <div>
                <label class="cwc-radio cwc-acc">
                    <input type="radio" id="useAnalysisYes" name="useAnalysis" value="yes"/>
                    <i class="cwc-radio-icon"></i>
                    我希望使用 Google Analytics 改进服务
                </label>
            </div>
            <div style="margin-bottom: 10px;">
                <label class="cwc-radio cwc-acc">
                    <input type="radio" id="useAnalysisNo" name="useAnalysis" value="no" checked/>
                    <i class="cwc-radio-icon"></i>
                    我不希望使用 Google Analytics
                </label>
            </div>
            <button class="cwc-btn cwc-acc" type="submit" id="analysisBtn"><span>保存更改</span></button>
        </form>
        
        <hr class="cwc-hr-solid">
        <strong class="cwc-subhead cwc-acc">正常使用 CWC 所必须的 Cookie</strong>
        <p class="cwc-acc">Caner Weather Channel 在使用时会通过存储名为 CWC-Profile 的 Cookie 以记录您的偏好设置与网站许可。这些数据以 JSON 格式存储，是提供个性化服务和 CWC 维持正常使用的重要组成部分。由于 CWC-Profile 对于网站功能至关重要，您无法在此页面禁用此 Cookie。但是，您有权在此查看存储于 CWC-Profile 中的数据，并且您可以删除它。<br><br>您个人的 CWC-Profile Cookie 数据将被安全的存储在本地，除 User ID 用于标识符，其他偏好与访问数据不会上传至 Caner HK 服务器。<br><br>请注意，删除 CWC-Profile 将导致您以往的偏好设置被清除。<br><br>为保障您的隐私安全，我们将每次偏好更新后刷新 CWC-Profile 的存储期限至 90 天。若超过这一期限未进行更新，CWC-Profile 将自动被删除。</p>
        <hr class="cwc-hr-dashed">
        
        <div class="cookie-container">
            <div style="margin-bottom: 15px;"><strong class="cwc-acc cwc-subhead-b">Cookie JSON 数据</strong></div>
            <div class="cookie-json cwc-acc">
                <?php echo htmlspecialchars($cookieJson); ?>
            </div>
            <hr class="cwc-hr-dashed">
            <strong class="cwc-subhead-b cwc-acc">Cookie 数据解释</strong>
            <div class="cwc-scrollable-container cwc-acc">
            <?php
            if (!empty($cookieData)) {
                echo '<ul>';
                foreach ($cookieData as $key => $value) {
                    echo "<li><strong>" . htmlspecialchars($key) . ": </strong>";
                    if (is_array($value)) {
                        $count = 1;
                        echo "<ul>";
                        foreach ($value as $subKey => $subValue) {
                            echo "<li><strong>" . $count . ":</strong><ul>";
                            if (is_array($subValue)) {
                                foreach ($subValue as $propertyKey => $propertyValue) {
                                    echo "<li><strong>" . htmlspecialchars($propertyKey) . ": </strong>" . htmlspecialchars($propertyValue) . "</li>";
                                }
                            } else {
                                echo "<li><strong>" . htmlspecialchars($subKey) . ": </strong>" . htmlspecialchars($subValue) . "</li>";
                            }
                            echo "</ul></li>";
                            $count++;
                        }
                        echo "</ul>";
                    } else {
                        echo htmlspecialchars($value);
                    }
                    echo "</li>";
                }
                echo '</ul>';
            } else {
                echo '<div style="margin: 10px;">没有 CWC-Profile Cookie 数据</div>';
            }
            ?>
            </div>
            
            <hr class="cwc-hr-dashed">
            <div><strong class="cwc-subhead-b cwc-acc">删除 CWC-Profile 数据</strong></div>
            
            <?php
            if (!empty($cookieData)) {
                echo '<p class="cwc-acc" style="color: #EF5350;"><strong id="delete-tip">注意，删除 CWC-Profile 会清除您的偏好设置，并重置 Caner Weather Channel 的使用数据。此操作不会删除第三方 Cookies。</strong></p>
            <button class="cwc-btn-delete cwc-acc" id="delete-cookie"><span id="button-text">删除 CWC-Profile Cookie 数据</span></button>';
            } else {
                echo '<p class="cwc-acc">没有 CWC-Profile Cookie 数据</p>';
            }
            ?>
            
        <hr class="cwc-hr-solid">
        <strong class="cwc-subhead cwc-acc">了解更多信息</strong>
        <p class="cwc-acc">想要了解更多有关 Caner Weather Channel 的条款以及各类政策信息吗？这些可能是您想要前往的页面！</p>
        <a href="https://weather.caner.hk/terms/" class="cwc-btn">条款与隐私权政策</a>
        <a href="https://github.com/Caner-HK/CWC-Caner-Weather-Channel/blob/main/LICENSE" class="cwc-btn">许可协议</a>
        <a href="https://github.com/Caner-HK/CWC-Caner-Weather-Channel/blob/main/SECURITY.md" class="cwc-btn">安全政策</a>
        </div>
        
        </div>
    
    </main>
    
    <footer class="footer">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 512 512" style="position: relative; top: 10px; margin-leftt: 10px; margin-bottom: 15px;" class="cwc-svg cwc-acc"  aria-label="Caner Weather Channel 的 LOGO"><path d="M340,480H106c-29.5,0-54.92-7.83-73.53-22.64C11.23,440.44,0,415.35,0,384.8c0-26.66,10.08-49.8,29.14-66.91,15.24-13.68,36.17-23.21,59-26.84h0c.06,0,.08,0,.09-.05,6.44-39,23.83-72.09,50.31-95.68A140.24,140.24,0,0,1,232,160c30.23,0,58.48,9.39,81.71,27.17a142.69,142.69,0,0,1,45.36,60.66c29.41,4.82,54.72,17.11,73.19,35.54C453,304.11,464,331.71,464,363.2c0,32.85-13.13,62.87-37,84.52C404.11,468.54,373.2,480,340,480Zm19-232.18Z"/><path d="M381.5,219.89a169.23,169.23,0,0,1,45.44,19A96,96,0,0,0,281,129.33q-2.85,2-5.54,4.2a162.47,162.47,0,0,1,57.73,28.23A174.53,174.53,0,0,1,381.5,219.89Z"/><rect x="448" y="192" width="64" height="32"/><rect x="320" y="32" width="32" height="64"/><path d="M255.35,129.63l12.45-12.45L223.18,72.55,200.55,95.18l33.17,33.17h.6A172,172,0,0,1,255.35,129.63Z"/><rect x="406.27" y="90.18" width="63.11" height="32" transform="translate(53.16 340.68) rotate(-45)"/></svg><br>
        <strong class="footer-head c-w-c cwc-acc">Caner Weather Channel</strong>
        <div style="margin-top: 8px;">
        <div class="cwc-typo">
        <blockquote class="cwc-acc">
        <span class="cwc-text-small">明天的天气怎么样？什么时候会下雨？台风即将来临吗？在 Caner Weather Channel 获取世界任何地点的全面天气预报！</span>
        </blockquote>
        </div>
        </div>
         <hr class="cwc-hr-double footer-border">
         <div class="follow-icon-container">
          <a href="https://x.com/CanerCente88952?t=w5n9qhcfgPVdRcx48NAibw&s=09" target="_blank" aria-label="前往Caner Twitter的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M496,109.5a201.8,201.8,0,0,1-56.55,15.3,97.51,97.51,0,0,0,43.33-53.6,197.74,197.74,0,0,1-62.56,23.5A99.14,99.14,0,0,0,348.31,64c-54.42,0-98.46,43.4-98.46,96.9a93.21,93.21,0,0,0,2.54,22.1,280.7,280.7,0,0,1-203-101.3A95.69,95.69,0,0,0,36,130.4C36,164,53.53,193.7,80,211.1A97.5,97.5,0,0,1,35.22,199v1.2c0,47,34,86.1,79,95a100.76,100.76,0,0,1-25.94,3.4,94.38,94.38,0,0,1-18.51-1.8c12.51,38.5,48.92,66.5,92.05,67.3A199.59,199.59,0,0,1,39.5,405.6,203,203,0,0,1,16,404.2,278.68,278.68,0,0,0,166.74,448c181.36,0,280.44-147.7,280.44-275.8,0-4.2-.11-8.4-.31-12.5A198.48,198.48,0,0,0,496,109.5Z"/></svg></a>
          <a href="https://www.facebook.com/profile.php?id=61556338823487&mibextid=ZbWKwL" target="_blank" aria-label="前往Caner Facebook的图标按钮"><svg xmlns="http://www.w3.org/2000/svg"  width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M480,257.35c0-123.7-100.3-224-224-224s-224,100.3-224,224c0,111.8,81.9,204.47,189,221.29V322.12H164.11V257.35H221V208c0-56.13,33.45-87.16,84.61-87.16,24.51,0,50.15,4.38,50.15,4.38v55.13H327.5c-27.81,0-36.51,17.26-36.51,35v42h62.12l-9.92,64.77H291V478.66C398.1,461.85,480,369.18,480,257.35Z" fill-rule="evenodd"/></svg></a>
          <a href="https://github.com/Caner-HK/CWC-Caner-Weather-Channel/" target="_blank" aria-label="前往CWC GitHub项目的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M256,32C132.3,32,32,134.9,32,261.7c0,101.5,64.2,187.5,153.2,217.9a17.56,17.56,0,0,0,3.8.4c8.3,0,11.5-6.1,11.5-11.4,0-5.5-.2-19.9-.3-39.1a102.4,102.4,0,0,1-22.6,2.7c-43.1,0-52.9-33.5-52.9-33.5-10.2-26.5-24.9-33.6-24.9-33.6-19.5-13.7-.1-14.1,1.4-14.1h.1c22.5,2,34.3,23.8,34.3,23.8,11.2,19.6,26.2,25.1,39.6,25.1a63,63,0,0,0,25.6-6c2-14.8,7.8-24.9,14.2-30.7-49.7-5.8-102-25.5-102-113.5,0-25.1,8.7-45.6,23-61.6-2.3-5.8-10-29.2,2.2-60.8a18.64,18.64,0,0,1,5-.5c8.1,0,26.4,3.1,56.6,24.1a208.21,208.21,0,0,1,112.2,0c30.2-21,48.5-24.1,56.6-24.1a18.64,18.64,0,0,1,5,.5c12.2,31.6,4.5,55,2.2,60.8,14.3,16.1,23,36.6,23,61.6,0,88.2-52.4,107.6-102.3,113.3,8,7.1,15.2,21.1,15.2,42.5,0,30.7-.3,55.5-.3,63,0,5.4,3.1,11.5,11.4,11.5a19.35,19.35,0,0,0,4-.4C415.9,449.2,480,363.1,480,261.7,480,134.9,379.7,32,256,32Z"/></svg></a>
          <a href="https://youtube.com/@CanerHK?si=2mNUf_XQtMukCIQI" target="_blank" aria-label="前往Caner YouTube频道的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" ><path d="M508.64,148.79c0-45-33.1-81.2-74-81.2C379.24,65,322.74,64,265,64H247c-57.6,0-114.2,1-169.6,3.6-40.8,0-73.9,36.4-73.9,81.4C1,184.59-.06,220.19,0,255.79q-.15,53.4,3.4,106.9c0,45,33.1,81.5,73.9,81.5,58.2,2.7,117.9,3.9,178.6,3.8q91.2.3,178.6-3.8c40.9,0,74-36.5,74-81.5,2.4-35.7,3.5-71.3,3.4-107Q512.24,202.29,508.64,148.79ZM207,353.89V157.39l145,98.2Z"/></svg></a>
          <a href="mailto:connect@caner.hk" target="_blank" aria-label="邮件联系Caner的图标按钮"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512" class="follow-icon cwc-svg" >
            <path d="M424,80H88a56.06,56.06,0,0,0-56,56V376a56.06,56.06,0,0,0,56,56H424a56.06,56.06,0,0,0,56-56V136A56.06,56.06,0,0,0,424,80Zm-14.18,92.63-144,112a16,16,0,0,1-19.64,0l-144-112a16,16,0,1,1,19.64-25.26L256,251.73,390.18,147.37a16,16,0,0,1,19.64,25.26Z"/>
        </svg></a>
        </div>
        
        <a href="https://weather.caner.hk/support/" target="_blank" class="cwc-footer-link link-space">支持</a>
        <a href="https://weather.caner.hk/tips/" target="_blank" class="cwc-footer-link link-space">使用提示</a>
        <a href="https://weather.caner.hk/pagemap/" target="_blank" class="cwc-footer-link">网站地图</a>
        <br>
        <a href="https://weather1.caner.hk/" target="_blank" class="cwc-footer-link link-space">WeatherKit CWC 预览版</a>
        <br>
        <a href="https://weather.caner.hk/cookies/" target="_blank" class="cwc-footer-link link-space">管理 CWC Cookies</a>
        <a href="https://donate.caner.hk/" target="_blank" class="cwc-footer-link link-space">关于 CWC</a>
        <a href="https://status.caner.center/" target="_blank" class="cwc-footer-link link-space">服务状态</a>
        <a href="https://donate.caner.hk/" target="_blank" class="cwc-footer-link">捐赠</a>
        <br>
        <a href="https://weather.caner.hk/feedback/" target="_blank" class="cwc-footer-link link-space">反馈错误或提供建议</a>
        <a href="https://weather.caner.hk/terms/" target="_blank" class="cwc-footer-link">条款与隐私权政策</a>
        <br><br>
        <a href="#top" class="cwc-footer-top">返回顶部 &uarr;</a>
         <hr class="cwc-hr-double footer-border">
         <div style="margin-bottom: 15px; margin-top: -5px;">
         <img class="cwc-acc"  aria-label="Caner 的 LOGO" style="height: 42px;" id="caner-logo" src="https://resource.caner.hk/get/logo/caner_logo_black.png"><br>
        <span class="cwc-text-small cwc-acc">CWC is a project which was designed and built by Caner HK.</span><br>
        <span class="cwc-text-small cwc-acc">&copy;&nbsp;Caner&nbsp;HK&nbsp;<span id="year"></span> - All Rights Reserved.</span>
        </div>
    </footer>
    <script>
    window.addEventListener('scroll', function() {
        var header = document.querySelector('header');
        if (window.scrollY > 0) {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                header.style.boxShadow = '0px 4px 2px -2px rgba(255,255,255,0.5)';
            } else {
                header.style.boxShadow = '0px 4px 2px -2px gray';
            }
        } else {
            header.style.boxShadow = 'none';
        }
    });
    
    document.querySelector('.header-btn').addEventListener('click', function() {
        this.style.transform = this.style.transform === 'rotate(180deg)' ? 'rotate(0deg)' : 'rotate(180deg)';
    });
    
    document.getElementById('menuBtn').addEventListener('click', function() {
        var menu = document.querySelector('.cwc-menu');
        if (menu.style.marginTop === '0px') {
            menu.style.marginTop = '-409px';
        } else {
            menu.style.marginTop = '0px';
        }
    
        var title = document.getElementById('cwc-title');
        title.classList.add('fade-out');
        setTimeout(function() {
            if (title.innerHTML.includes("菜单")) {
                title.innerHTML = '<a href="https://weather.caner.hk" style="text-decoration: none; color: inherit; visited: inherit;"><span class="hide-s">Caner </span>Weather<span class="hide-xs"> Channel</span></a>';
            } else {
                title.innerHTML = '<a href="https://weather.caner.hk" style="text-decoration: none; color: inherit; visited: inherit; position: relative; top: -2px; font-size: 22px;">CWC <span class="hide-s">导航</span>菜单</a>';
            }
            title.classList.remove('fade-out');
            title.classList.add('fade-in');
    
            setTimeout(function() {
                title.classList.remove('fade-in');
            }, 250);
        }, 250);
    });
    
    var isMenuBtnVisible = true;
    function fadeIn(element) {
        element.style.display = 'block';
        element.classList.remove('fade-out');
        element.classList.add('fade-in');
    }
    
    function fadeOut(element) {
        element.classList.remove('fade-in');
        element.classList.add('fade-out');
        element.addEventListener('animationend', function() {
            if (element.classList.contains('fade-out')) {
                element.style.display = 'none';
            }
        }, { once: true });
    }
    
    document.addEventListener('scroll', function() {
        var menuBtn = document.getElementById('menuBtn');
        var backtopBtn = document.getElementById('backtopBtn');
        if (window.scrollY > 350 && isMenuBtnVisible) {
            fadeOut(menuBtn);
            fadeIn(backtopBtn);
            isMenuBtnVisible = false;
        } else if (window.scrollY <= 350 && !isMenuBtnVisible) {
            fadeOut(backtopBtn);
            fadeIn(menuBtn);
            isMenuBtnVisible = true;
        }
    });
    
    document.getElementById('year').textContent = new Date().getFullYear();
    
    document.getElementById('backtopBtn').addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        var logo = document.getElementById('caner-logo');
        var matchMedia = window.matchMedia('(prefers-color-scheme: dark)');
        function updateLogo() {
            if (matchMedia.matches) {
                logo.src = 'https://resource.caner.hk/get/logo/caner-logo-white.png';
            } else {
                logo.src = 'https://resource.caner.hk/get/logo/caner_logo_black.png';
            }
        }
        matchMedia.addListener(updateLogo);
        updateLogo();
        
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('#cwc-analytics');
        var useAnalysisYes = document.querySelector('#useAnalysisYes');
        var useAnalysisNo = document.querySelector('#useAnalysisNo');
        var cookieValue = getCookie('CWC-Profile');
        if (cookieValue) {
            var cookieObj = JSON.parse(decodeURIComponent(cookieValue));
            if (cookieObj["Google Analytics"] === "true") {
                useAnalysisYes.checked = true;
            } else {
                useAnalysisNo.checked = true;
            }
        } else {
            useAnalysisNo.checked = true;
        }
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var useAnalysis = useAnalysisYes.checked ? "true" : "false";
            var now = new Date();
            var expirationTime = new Date(now.getTime() + 90*24*60*60*1000);
            var newCookieObj = cookieValue ? JSON.parse(decodeURIComponent(cookieValue)) : {};
            newCookieObj["Google Analytics"] = useAnalysis;
            newCookieObj["Expiration"] = expirationTime.toISOString();
            document.cookie = "CWC-Profile=" + encodeURIComponent(JSON.stringify(newCookieObj)) + ";path=/;expires=" + expirationTime.toUTCString();
            var btnTextSpan = document.querySelector('#analysisBtn span');
            animateFadeOut(btnTextSpan, function() {
                btnTextSpan.textContent = "Google Analytics 偏好已设置";
                animateFadeIn(btnTextSpan);
            });
        });
    });
    
    function animateFadeOut(element, callback) {
        element.style.transition = 'opacity 0.3s';
        element.style.opacity = 0;
        setTimeout(callback, 300);
    }
    
    function animateFadeIn(element) {
        element.style.opacity = 0;
        setTimeout(function() {
            element.style.transition = 'opacity 0.3s';
            element.style.opacity = 1;
        }, 10);
    }
    
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }
    
    document.getElementById('location').addEventListener('keyup', function() {
        var input = this.value;
        var suggestions = document.getElementById('suggestions');
        var closeBtn = document.getElementById('closeBtn');
    
        if (input.length >= 1) {
            closeBtn.style.display = 'block';
            fetch(`https://weather.caner.hk/external/search_suggestions.php?input=${encodeURIComponent(input)}`)
                .then(response => response.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    data.predictions.forEach(function(prediction) {
                        var li = document.createElement('li');
                        li.textContent = prediction.description;
                        li.addEventListener('click', function() {
                            document.getElementById('location').value = prediction.description;
                            suggestions.style.opacity = '0';
                            suggestions.style.transform = 'scaleY(0)';
                            setTimeout(function() {
                                suggestions.style.display = 'none';
                            }, 300);
                            closeBtn.style.display = 'none';
                        });
                        suggestions.appendChild(li);
                    });
                    if (suggestions.childElementCount > 0) {
                        suggestions.style.display = 'block';
                        setTimeout(function() {
                            suggestions.style.opacity = '1';
                            suggestions.style.transform = 'scaleY(1)';
                        }, 10);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    suggestions.style.opacity = '0';
                    suggestions.style.transform = 'scaleY(0)';
                    setTimeout(function() {
                        suggestions.style.display = 'none';
                    }, 300);
                });
        } else {
            suggestions.style.opacity = '0';
            suggestions.style.transform = 'scaleY(0)';
            setTimeout(function() {
                suggestions.style.display = 'none';
            }, 300);
            closeBtn.style.display = 'none';
        }
    });
    
    document.getElementById('closeBtn').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('location').value = '';
        document.getElementById('suggestions').style.opacity = '0';
        document.getElementById('suggestions').style.transform = 'scaleY(0)';
        setTimeout(function() {
            document.getElementById('suggestions').style.display = 'none';
        }, 300);
        document.getElementById('closeBtn').style.display = 'none';
    });

    document.addEventListener('DOMContentLoaded', function () {
        const buttons = [
            { href: "https://weather.caner.hk/?location=英国伦敦", text: "英国 伦敦" },
            { href: "https://weather.caner.hk/?location=美国加利福尼亚州洛杉矶", text: "美国 加利福尼亚州洛杉矶" },
            { href: "https://weather.caner.hk/?location=香港", text: "香港" },
            { href: "https://weather.caner.hk/?location=北京市", text: "中国 北京市" },
            { href: "https://weather.caner.hk/?location=天津市", text: "中国 天津市" },
            { href: "https://weather.caner.hk/?location=上海市", text: "中国 上海市" },
            { href: "https://weather.caner.hk/?location=东京", text: "日本 东京" },
            { href: "https://weather.caner.hk/?location=巴黎", text: "法国 巴黎" },
            { href: "https://weather.caner.hk/?location=纽约", text: "美国 纽约" },
            { href: "https://weather.caner.hk/?location=悉尼", text: "澳大利亚 悉尼" },
            { href: "https://weather.caner.hk/?location=罗马", text: "意大利 罗马" },
            { href: "https://weather.caner.hk/?location=布宜诺斯艾利斯", text: "阿根廷 布宜诺斯艾利斯" },
            { href: "https://weather.caner.hk/?location=开普敦", text: "南非 开普敦" },
            { href: "https://weather.caner.hk/?location=曼谷", text: "泰国 曼谷" },
            { href: "https://weather.caner.hk/?location=伊斯坦布尔", text: "土耳其 伊斯坦布尔" },
            { href: "https://weather.caner.hk/?location=里约热内卢", text: "巴西 里约热内卢" },
            { href: "https://weather.caner.hk/?location=阿姆斯特丹", text: "荷兰 阿姆斯特丹" },
            { href: "https://weather.caner.hk/?location=圣彼得堡", text: "俄罗斯 圣彼得堡" },
            { href: "https://weather.caner.hk/?location=维也纳", text: "奥地利 维也纳" },
            { href: "https://weather.caner.hk/?location=慕尼黑", text: "德国 慕尼黑" },
            { href: "https://weather.caner.hk/?location=温哥华", text: "加拿大 温哥华" },
            { href: "https://weather.caner.hk/?location=斯德哥尔摩", text: "瑞典 斯德哥尔摩" },
            { href: "https://weather.caner.hk/?location=赫尔辛基", text: "芬兰 赫尔辛基" },
            { href: "https://weather.caner.hk/?location=里斯本", text: "葡萄牙 里斯本" },
            { href: "https://weather.caner.hk/?location=布拉格", text: "捷克 布拉格" },
            { href: "https://weather.caner.hk/?location=布达佩斯", text: "匈牙利 布达佩斯" },
            { href: "https://weather.caner.hk/?location=哥本哈根", text: "丹麦 哥本哈根" },
            { href: "https://weather.caner.hk/?location=奥斯陆", text: "挪威 奥斯陆" },
            { href: "https://weather.caner.hk/?location=都柏林", text: "爱尔兰 都柏林" },
            { href: "https://weather.caner.hk/?location=雅典", text: "希腊 雅典" },
            { href: "https://weather.caner.hk/?location=首尔", text: "韩国 首尔" },
            { href: "https://weather.caner.hk/?location=奥克兰", text: "新西兰 奥克兰" },
            { href: "https://weather.caner.hk/?location=威海", text: "中国 山东省威海市" },
            { href: "https://weather.caner.hk/?location=马德里", text: "西班牙 马德里" },
            { href: "https://weather.caner.hk/?location=墨尔本", text: "澳大利亚 墨尔本" },
            { href: "https://weather.caner.hk/?location=蒙特利尔", text: "加拿大 蒙特利尔" },
            { href: "https://weather.caner.hk/?location=开罗", text: "埃及 开罗" },
            { href: "https://weather.caner.hk/?location=里约热内卢", text: "巴西 里约热内卢" },
            { href: "https://weather.caner.hk/?location=波哥大", text: "哥伦比亚 波哥大" },
            { href: "https://weather.caner.hk/?location=圣保罗", text: "巴西 圣保罗" },
            { href: "https://weather.caner.hk/?location=伊斯坦布尔", text: "土耳其 伊斯坦布尔" },
            { href: "https://weather.caner.hk/?location=雅加达", text: "印度尼西亚 雅加达" },
            { href: "https://weather.caner.hk/?location=利马", text: "秘鲁 利马" },
            { href: "https://weather.caner.hk/?location=内罗毕", text: "肯尼亚 内罗毕" },
            { href: "https://weather.caner.hk/?location=吉隆坡", text: "马来西亚 吉隆坡" },
            { href: "https://weather.caner.hk/?location=达卡", text: "孟加拉国 达卡" },
        ];
    
        function getRandomButtons(n) {
            const shuffled = buttons.sort(() => 0.5 - Math.random());
            return shuffled.slice(0, n);
        }
    
        function displayButtons() {
            const btnGroup = document.getElementById('btn-group');
            btnGroup.innerHTML = '';
            const selectedButtons = getRandomButtons(4);
    
            selectedButtons.forEach(btn => {
                const link = document.createElement('a');
                link.href = btn.href;
                link.className = 'cwc-btn';
                link.textContent = btn.text;
                btnGroup.appendChild(link);
            });
        }
        displayButtons();
    });
        
    var isConfirmed = false;
    
    document.getElementById('delete-cookie').addEventListener('click', function() {
        var btn = this;
        var buttonText = document.getElementById('button-text');
        var deleteTip = document.getElementById('delete-tip');
    
        if (!btn.dataset.confirmed) {
            buttonText.classList.add('text-fade-out');
            setTimeout(function() {
                buttonText.textContent = '再次点击以确认删除数据';
                btn.dataset.confirmed = 'true';
                buttonText.classList.remove('text-fade-out');
                buttonText.classList.add('text-fade-in');
                setTimeout(function() {
                    buttonText.classList.remove('text-fade-in');
                }, 300);
            }, 300);
        } else {
            deleteTip.classList.add('tip-fade-out');
            setTimeout(function() {
                document.cookie = 'CWC-Profile=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                deleteTip.textContent = '您的偏好设置已被清除。继续使用 Caner Weather Channel 将重新生成此 Cookie。若不愿存储，可于浏览器设置中禁用此网站的 Cookies 权限。';
                btn.disabled = true;
                btn.style.border = '1px solid #ddd';
                btn.style.backgroundColor = '#ddd';
                buttonText.style.color = 'white';
                buttonText.classList.add('text-fade-out');
                setTimeout(function() {
                    buttonText.textContent = 'CWC-Profile Cookie 已删除';
                    buttonText.classList.remove('text-fade-out');
                    buttonText.classList.add('text-fade-in');
                    deleteTip.classList.remove('tip-fade-out');
                    deleteTip.classList.add('tip-fade-in');
                    setTimeout(function() {
                        buttonText.classList.remove('text-fade-in');
                        deleteTip.classList.remove('tip-fade-in');
                    }, 350);
                }, 300);
            }, 350);
        }
    });
    
    document.querySelector('.cwc-menu-search').addEventListener('submit', function(event) {
        var input = document.getElementById('location');
        var btn = document.getElementById('btn');
        if (!input.value.trim()) {
            event.preventDefault();
            input.classList.add('shake');
            btn.classList.add('shake');
            var originalPlaceholder = input.placeholder;
            input.placeholder = '请填写查询地点';
            setTimeout(function() {
                input.placeholder = originalPlaceholder;
            }, 3000);
            setTimeout(function() {
                input.classList.remove('shake');
                btn.classList.remove('shake');
            }, 500);
        }
    });
    
    document.getElementById('acc-checkbox').addEventListener('change', function(event) {
        var elements = document.querySelectorAll('.cwc-acc, a, button');
        elements.forEach(function(element) {
            if (event.target.checked) {
                element.setAttribute('tabindex', '0');
                element.classList.add('cwc-acc-style');
            } else {
                element.removeAttribute('tabindex');
                element.classList.remove('cwc-acc-style');
            }
        });
        if (event.target.checked) {
            enableTTS();
        } else {
            disableTTS();
        }
    });
    
    function enableTTS() {
        document.addEventListener('focus', ttsFocusHandler, true);
    }
    
    function disableTTS() {
        document.removeEventListener('focus', ttsFocusHandler, true);
    }
    
    function ttsFocusHandler(e) {
        if (e.target.classList.contains('cwc-acc-style')) {
            window.speechSynthesis.cancel();
            var textToSpeak = "";
            if (e.target.tagName.toLowerCase() === 'a') {
                textToSpeak = "链接：" + (e.target.textContent || e.target.innerText || e.target.getAttribute('aria-label'));
            } else if (e.target.tagName.toLowerCase() === 'button') {
                textToSpeak = "按钮：" + (e.target.textContent || e.target.innerText || e.target.getAttribute('aria-label'));
            } else {
                textToSpeak = e.target.textContent || e.target.innerText || e.target.getAttribute('aria-label');
            }
            if (textToSpeak) {
                var msg = new SpeechSynthesisUtterance(textToSpeak);
                window.speechSynthesis.speak(msg);
            }
        }
    }
    </script>
</body>
</html>