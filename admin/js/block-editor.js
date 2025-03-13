/**
 * Personas Block Editor Integration
 *
 * Adds sidebar panel for persona selection and preview in the block editor.
 *
 * @package
 * @version    1.2.0
 */

(function () {
	const { __ } = wp.i18n;
	const { registerPlugin } = wp.plugins;
	const { PluginSidebar, PluginSidebarMoreMenuItem } = wp.editPost;
	const { PanelBody, SelectControl, Button, Notice } = wp.components;
	const { useSelect } = wp.data;
	const { Fragment, useState, useEffect } = wp.element;

	/**
	 * Persona Selector Component
	 *
	 * Allows selecting and previewing content for different personas.
	 */
	const PersonaSelector = () => {
		// State for the selected persona
		const [selectedPersona, setSelectedPersona] = useState('default');
		const [previewLoading, setPreviewLoading] = useState(false);
		const [previewError, setPreviewError] = useState(null);
		const [previewAvailable, setPreviewAvailable] = useState(false);

		// Get the current post ID and editor state
		const { postId, personas } = useSelect((select) => {
			return {
				postId: select('core/editor').getCurrentPostId(),
				personas: window.cmePersonasAdmin?.personas || {
					default: __('Default', 'cme-personas'),
				},
			};
		}, []);

		// Create options for the dropdown
		const personaOptions = Object.entries(personas).map(
			([value, label]) => {
				return { value, label };
			}
		);

		/**
		 * Check if preview is available for the selected persona
		 */
		const checkPreviewAvailability = () => {
			if (selectedPersona === 'default' || !postId) {
				setPreviewAvailable(false);
				return;
			}

			setPreviewLoading(true);
			setPreviewError(null);

			// Make AJAX request to check if content exists for this persona
			const data = new FormData();
			data.append('action', 'cme_check_persona_content');
			data.append('post_id', postId);
			data.append('persona', selectedPersona);
			data.append('nonce', window.cmePersonasAdmin.nonce);

			fetch(window.cmePersonasAdmin.ajaxUrl, {
				method: 'POST',
				body: data,
				credentials: 'same-origin',
			})
				.then((response) => response.json())
				.then((response) => {
					if (response.success) {
						setPreviewAvailable(response.data.hasContent);
					} else {
						setPreviewError(
							response.data.message ||
								__(
									'Error checking preview availability',
									'cme-personas'
								)
						);
						setPreviewAvailable(false);
					}
					setPreviewLoading(false);
				})
				.catch(() => {
					setPreviewError(__('Network error', 'cme-personas'));
					setPreviewAvailable(false);
					setPreviewLoading(false);
				});
		};

		/**
		 * Handle preview button click
		 */
		const handlePreviewClick = () => {
			if (!previewAvailable || selectedPersona === 'default') {
				return;
			}

			// Open the preview in a new dialog
			// This uses the existing preview function from the admin.js file
			if (
				typeof window.PersonaAdmin !== 'undefined' &&
				typeof window.PersonaAdmin.showContentPreview === 'function'
			) {
				window.PersonaAdmin.showContentPreview(postId, selectedPersona);
			} else {
				// Fallback if PersonaAdmin is not available
				window.open(
					`${window.location.origin}/wp-admin/admin-ajax.php?action=cme_preview_persona_content&post_id=${postId}&persona=${selectedPersona}&nonce=${window.cmePersonasAdmin.nonce}`,
					'personaPreview',
					'width=800,height=600,resizable=yes,scrollbars=yes'
				);
			}
		};

		// Define the effect function to check availability when relevant dependencies change
		useEffect(() => {
			checkPreviewAvailability();
		}, [selectedPersona, postId]);

		return (
			<Fragment>
				<PanelBody
					title={__('Persona Content', 'cme-personas')}
					initialOpen={true}
				>
					<div className="persona-selector">
						<SelectControl
							label={__('Preview as Persona', 'cme-personas')}
							value={selectedPersona}
							options={personaOptions}
							onChange={setSelectedPersona}
							className="personas-list"
						/>

						{previewError && (
							<Notice status="error" isDismissible={false}>
								{previewError}
							</Notice>
						)}

						<Button
							isPrimary
							disabled={!previewAvailable || previewLoading}
							onClick={handlePreviewClick}
							className="preview-button"
							isBusy={previewLoading}
						>
							{previewLoading
								? __('Checking…', 'cme-personas')
								: __('Preview Persona Content', 'cme-personas')}
						</Button>

						{!previewAvailable &&
							selectedPersona !== 'default' &&
							!previewLoading && (
								<p className="no-content-message">
									{__(
										'No content exists for this persona yet. Add content in the Persona Content meta box below the editor.',
										'cme-personas'
									)}
								</p>
							)}
					</div>
				</PanelBody>
			</Fragment>
		);
	};

	registerPlugin('cme-personas', {
		icon: 'admin-users',
		render: () => {
			return (
				<Fragment>
					<PluginSidebarMoreMenuItem target="cme-personas-sidebar">
						{__('Persona Content', 'cme-personas')}
					</PluginSidebarMoreMenuItem>
					<PluginSidebar
						name="cme-personas-sidebar"
						title={__('Persona Content', 'cme-personas')}
					>
						<PersonaSelector />
					</PluginSidebar>
				</Fragment>
			);
		},
	});
})();
