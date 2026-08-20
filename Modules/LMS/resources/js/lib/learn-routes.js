/**
 * Central place for every "Learn" (student-facing LMS) URL.
 * Import this instead of hardcoding paths in components, so a route
 * change only needs to happen in one place.
 */
export const learnRoutes = {
    home: '/lms/learn',
    browse: '/lms/learn/browse',
    myCourses: '/lms/my-courses',
    login: '/tenant-login',

    /** @param {number|string} courseId */
    courseShow: (courseId) => `/lms/learn/browse/${courseId}`,

    /** Compact enrolled-only course view (curriculum-first), reached from My Courses */
    myCourseShow: (courseId) => `/lms/my-courses/${courseId}`,

    /** @param {number|string} courseId */
    enrollCourse: (courseId) => `/lms/learn/browse/${courseId}/enroll`,

    /** @param {number|string} lessonId */
    lessonShow: (lessonId) => `/lms/learn/${lessonId}`,

    /** @param {number|string} lessonId */
    trackVideo: (lessonId) => `/lms/learn/${lessonId}/track-video`,

    /** @param {number|string} lessonId */
    markEbookRead: (lessonId) => `/lms/learn/${lessonId}/mark-ebook-read`,

    /** @param {number|string} lessonId */
    saveNote: (lessonId) => `/lms/learn/${lessonId}/notes`,

    /** @param {number|string} quizId */
    quizStart: (quizId) => `/lms/quizzes/${quizId}/start`,

    /** @param {number|string} attemptId */
    quizSubmit: (attemptId) => `/lms/quiz-attempts/${attemptId}/submit`,

    /** @param {number|string} courseId */
    submitReview: (courseId) => `/lms/courses/${courseId}/reviews`,

    myOrders: '/lms/my-orders',

    /** @param {number|string} orderId */
    orderInvoice: (orderId) => `/lms/my-orders/${orderId}/invoice`,

    /** @param {number|string} assignmentId */
    submitAssignment: (assignmentId) => `/lms/assignments/${assignmentId}/submit`,

    /** @param {number|string} courseId */
    leaderboard: (courseId) => `/lms/courses/${courseId}/leaderboard`,

    /** @param {number|string} courseId */
    leaderboardJson: (courseId) => `/lms/courses/${courseId}/leaderboard.json`,

    /** @param {number|string} courseId */
    downloadCertificate: (courseId) => `/lms/courses/${courseId}/certificate`,

    /** @param {number|string} categoryId */
    browseByCategory: (categoryId) => `/lms/learn/browse?category_id=${categoryId}`,
};