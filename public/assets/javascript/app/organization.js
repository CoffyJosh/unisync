$(document).ready(function () {

    const BROWSE_URL = 'browse.php';

    // If the server says the organization no longer exists, tell the user and leave the page.
    function handleOrgGone(response) {
        if (response && response.organization_gone) {
            alert(response.message || 'This organization no longer exists.');
            window.location.href = BROWSE_URL;
            return true;
        }
        return false;
    }

    $('.org-tab').on('click', function () {

        const selectedTab = $(this).data('tab');

        // Hide all tab panels.
        $('.org-panel').addClass('hidden');

        // Show selected panel.
        $('#tab-' + selectedTab).removeClass('hidden');

        // Reset tab styles.
        $('.org-tab')
            .removeClass('border-[#1E3A5F] text-[#1E3A5F]')
            .addClass('border-transparent text-slate-500');

        // Highlight active tab.
        $(this)
            .removeClass('border-transparent text-slate-500')
            .addClass('border-[#1E3A5F] text-[#1E3A5F]');

    });

    const $modal = $('#editOrganizationModal');

    $('#openEditOrganization').on('click', function () {
        $modal.removeClass('hidden').addClass('flex');
    });

    $('#closeEditOrganization, #cancelEditOrganization').on('click', function () {
        $modal.addClass('hidden').removeClass('flex');
    });

    // Close when clicking the dark background
    $modal.on('click', function (e) {
        if (e.target === this) {
            $modal.addClass('hidden').removeClass('flex');
        }
    });

    // Close with Escape
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $modal.addClass('hidden').removeClass('flex');
        }
    });

    // Request to Join Organization (creates a pending request)
    $('#joinOrganization').on('click', function () {
        const button = $(this);
        const organizationId = button.data('organization-id');

        button.prop('disabled', true).text('Sending...');

        $.ajax({
            url: '../php/app/organization/join_organization.php',
            type: 'POST',
            dataType: 'json',
            data: {
                organization_id: organizationId
            },
            success: function (response) {
                if (handleOrgGone(response)) {
                    return;
                }

                alert(response.message);

                if (response.success) {
                    window.location.reload();
                } else {
                    button.prop('disabled', false).text('Request to Join');
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;

                if (handleOrgGone(response)) {
                    return;
                }

                alert(response?.message || 'Unable to send your request.');

                button.prop('disabled', false).text('Request to Join');
            }
        });
    });

    // Cancel a pending join request (the membership becomes 'deleted')
    $('#cancelJoinRequest').on('click', function () {
        const button = $(this);
        const originalHtml = button.html();
        const organizationId = button.data('organization-id');

        if (!confirm('Cancel your request to join this organization?')) {
            return;
        }

        button.prop('disabled', true).text('Cancelling...');

        $.ajax({
            url: '../php/app/organization/cancel_join_request.php',
            type: 'POST',
            dataType: 'json',
            data: {
                organization_id: organizationId
            },
            success: function (response) {
                if (handleOrgGone(response)) {
                    return;
                }

                alert(response.message);

                // Reload either way so the page shows the real current state
                // (e.g. the request was approved while the page was open).
                window.location.reload();
            },
            error: function (xhr) {
                const response = xhr.responseJSON;

                if (handleOrgGone(response)) {
                    return;
                }

                alert(response?.message || 'Unable to cancel your request.');

                button.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Admin toggle on the Members tab: approved members <-> pending requests
    const $viewToggle = $('#membersViewToggle');

    if ($viewToggle.length) {
        const viewKey = 'unisync_org_members_view_' + $viewToggle.data('organization-id');

        function showMembersView(view) {
            const showRequests = view === 'requests';

            $('#members-view-members').toggleClass('hidden', showRequests);
            $('#members-view-requests').toggleClass('hidden', !showRequests);

            $('.members-view-btn').each(function () {
                const isActive = $(this).data('view') === view;

                $(this)
                    .toggleClass('bg-white text-[#1E3A5F] shadow-sm', isActive)
                    .toggleClass('text-slate-500 hover:text-[#1E3A5F]', !isActive);
            });

            try { sessionStorage.setItem(viewKey, view); } catch (err) {}
        }

        $('.members-view-btn').on('click', function () {
            showMembersView($(this).data('view'));
        });

        // Restore the last view (e.g. after accepting or rejecting a request).
        let savedView = null;
        try { savedView = sessionStorage.getItem(viewKey); } catch (err) {}

        if (savedView === 'requests') {
            showMembersView('requests');
        }
    }

    // Accept / Reject a pending join request (Admin only)
    $(document).on('click', '.review-membership', function () {
        const button = $(this);
        const decision = button.data('decision');
        const membershipId = button.data('membership-id');
        const memberName = button.data('member-name');
        const $rowButtons = button.closest('.request-row').find('.review-membership');
        const originalText = button.text();

        const question = decision === 'approve'
            ? 'Accept ' + memberName + ' into this organization?'
            : 'Reject ' + memberName + '\'s request to join?';

        if (!confirm(question)) {
            return;
        }

        $rowButtons.prop('disabled', true);
        button.text(decision === 'approve' ? 'Accepting...' : 'Rejecting...');

        $.ajax({
            url: '../php/app/organization/review_membership.php',
            type: 'POST',
            dataType: 'json',
            data: {
                membership_id: membershipId,
                decision: decision
            },
            success: function (response) {
                if (handleOrgGone(response)) {
                    return;
                }

                alert(response.message);

                // Reload either way so the lists and counts reflect the real state.
                window.location.reload();
            },
            error: function (xhr) {
                if (handleOrgGone(xhr.responseJSON)) {
                    return;
                }

                alert(xhr.responseJSON?.message || 'Unable to review this request.');

                $rowButtons.prop('disabled', false);
                button.text(originalText);
            }
        });
    });

    // Leave Organization (hero button and the Leave button in the member list)
    $('#leaveOrganization, .leave-member').on('click', function () {
        const button = $(this);
        const originalHtml = button.html();
        const organizationId = button.data('organization-id');
        const role = button.data('role');
        const memberCount = parseInt(button.data('member-count'), 10);

        // First confirmation
        if (!confirm('Are you sure you want to leave this organization?')) {
            return;
        }

        // Second confirmation if the Admin is the only member
        if (role === 'Admin' && memberCount <= 1) {
            const confirmDelete = confirm(
                'You are the only remaining member. Leaving will permanently deactivate this organization. Are you sure you want to continue?'
            );

            if (!confirmDelete) {
                return;
            }
        }

        button.prop('disabled', true).text('Leaving...');

        $.ajax({
            url: '../php/app/organization/leave_organization.php',
            type: 'POST',
            dataType: 'json',
            data: {
                organization_id: organizationId
            },
            success: function (response) {
                if (handleOrgGone(response)) {
                    return;
                }

                alert(response.message);

                if (response.success) {
                    if (response.organization_deleted) {
                        window.location.href = BROWSE_URL;
                    } else {
                        window.location.reload();
                    }
                } else {
                    button.prop('disabled', false).html(originalHtml);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;

                if (handleOrgGone(response)) {
                    return;
                }

                alert(response?.message || 'Unable to leave organization.');

                button.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Role colors (the dropdown's text, border and arrow all follow the wrapper's text color)
    const roleTextClasses = {
        Admin: 'text-blue-700',
        Manager: 'text-amber-600',
        Member: 'text-green-700'
    };
    const allRoleTextClasses = Object.values(roleTextClasses).join(' ');

    // Update Member Role (saves automatically when the dropdown changes)
    $(document).on('change', '.member-role-select', function () {
        const $select = $(this);
        const $wrap = $select.closest('.role-wrap');

        const membershipId = $select.data('membership-id');
        const isSelf = $select.attr('data-is-self') === 'true';
        const previousRole = $select.attr('data-current-role');
        const newRole = $select.val();

        if (newRole === previousRole) {
            return;
        }

        function revertSelection() {
            $select.val(previousRole);
        }

        // Extra checks when an Admin changes their own role.
        if (isSelf && newRole !== 'Admin') {
            const otherAdmins = $('.member-role-select').filter(function () {
                return $(this).attr('data-current-role') === 'Admin'
                    && $(this).attr('data-is-self') !== 'true';
            }).length;

            if (otherAdmins === 0) {
                alert('You are the only Admin of this organization. Promote another member to Admin before changing your own role.');
                revertSelection();
                return;
            }

            if (!confirm('Warning: you are about to demote yourself from Admin to ' + newRole + '. You will lose the ability to manage roles. Do you want to continue?')) {
                revertSelection();
                return;
            }
        }

        $select.prop('disabled', true);

        $.ajax({
            url: '../php/app/organization/update_member_role.php',
            type: 'POST',
            dataType: 'json',
            data: {
                membership_id: membershipId,
                role: newRole
            },
            success: function (response) {
                if (handleOrgGone(response)) {
                    return;
                }

                if (!response.success) {
                    alert(response.message);
                    revertSelection();
                    $select.prop('disabled', false);
                    return;
                }

                const savedRole = response.role || newRole;

                // Reflect the saved role immediately.
                $select.val(savedRole);
                $select.attr('data-current-role', savedRole);
                $wrap
                    .removeClass(allRoleTextClasses)
                    .addClass(roleTextClasses[savedRole] || 'text-slate-600');

                $select.prop('disabled', false);

                // Your own permissions just changed, so reload to refresh the page.
                if (response.is_self) {
                    alert(response.message);
                    window.location.reload();
                }
            },
            error: function (xhr) {
                if (handleOrgGone(xhr.responseJSON)) {
                    return;
                }

                alert(xhr.responseJSON?.message || 'Unable to update member role.');
                revertSelection();
                $select.prop('disabled', false);
            }
        });
    });

    // Kick Member
    $(document).on('click', '.kick-member', function () {
        const button = $(this);
        const membershipId = button.data('membership-id');
        const memberName = button.data('member-name');

        if (!confirm('Are you sure you want to kick ' + memberName + ' from this organization?')) {
            return;
        }

        button.prop('disabled', true).text('Kicking...');

        $.ajax({
            url: '../php/app/organization/kick_member.php',
            type: 'POST',
            dataType: 'json',
            data: {
                membership_id: membershipId
            },
            success: function (response) {
                if (handleOrgGone(response)) {
                    return;
                }

                alert(response.message);

                if (response.success) {
                    window.location.reload();
                } else {
                    button.prop('disabled', false).text('Kick');
                }
            },
            error: function (xhr) {
                if (handleOrgGone(xhr.responseJSON)) {
                    return;
                }

                alert(xhr.responseJSON?.message || 'Unable to kick this member.');
                button.prop('disabled', false).text('Kick');
            }
        });
    });

    // Delete Organization (Admin only, from the Edit Organization modal)
    $('#deleteOrganization').on('click', function () {
        const button = $(this);
        const organizationId = button.data('organization-id');
        const organizationName = String(button.data('organization-name'));

        const typed = prompt(
            'This will delete the organization for all members.\n\n' +
            'Type the organization name to confirm:\n' + organizationName
        );

        if (typed === null) {
            return;
        }

        if (typed.trim() !== organizationName.trim()) {
            alert('The name did not match. The organization was not deleted.');
            return;
        }

        button.prop('disabled', true).text('Deleting...');

        $.ajax({
            url: '../php/app/organization/delete_organization.php',
            type: 'POST',
            dataType: 'json',
            data: {
                organization_id: organizationId
            },
            success: function (response) {
                if (handleOrgGone(response)) {
                    return;
                }

                alert(response.message);

                if (response.success) {
                    window.location.href = BROWSE_URL;
                } else {
                    button.prop('disabled', false).text('Delete Organization');
                }
            },
            error: function (xhr) {
                if (handleOrgGone(xhr.responseJSON)) {
                    return;
                }

                alert(xhr.responseJSON?.message || 'Unable to delete the organization.');
                button.prop('disabled', false).text('Delete Organization');
            }
        });
    });
});