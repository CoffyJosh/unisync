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

    // Join Organization
    $('#joinOrganization').on('click', function () {
        const button = $(this);
        const organizationId = button.data('organization-id');

        button.prop('disabled', true).text('Joining...');

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
                    button.prop('disabled', false).text('Join Organization');
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;

                if (handleOrgGone(response)) {
                    return;
                }

                alert(response?.message || 'Unable to join organization.');

                button.prop('disabled', false).text('Join Organization');
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

        if (typed.trim() !== organizationName) {
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

    $(document).on('click', '#deleteOrganization', function () {
        if (!confirm('Delete this organization? This cannot be undone.')) return;

        const $btn = $(this).prop('disabled', true);

        $.ajax({
            url: '../php/app/organization/delete_organization.php',
            type: 'POST',
            dataType: 'json',
            data: { organization_id: $btn.data('organization-id') },
            success: function (res) {
                if (res.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert(res.message);
                    $btn.prop('disabled', false);
                }
            },
            error: function (xhr) {
                const res = xhr.responseJSON || {};
                if (res.organization_gone) {
                    alert(res.message);
                    window.location.href = 'dashboard.php';
                    return;
                }
                alert(res.message || 'Something went wrong. Please try again.');
                $btn.prop('disabled', false);
            }
        });
    });
});