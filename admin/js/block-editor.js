/**
 * Personas Block Editor Integration
 *
 * Adds sidebar panel for persona selection and guidance in the block editor.
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
	const { Fragment, useState } = wp.element;

	/**
	 * Persona Selector Component
	 *
	 * Shows help for working with persona shortcodes.
	 */
	const PersonaSelector = () => {
		// State for the selected persona (for showing shortcode examples)
		const [selectedPersona, setSelectedPersona] = useState('default');

		// Get the current post ID and editor state
		const { personas } = useSelect(() => {
			return {
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
		 * Handle inserting shortcode
		 */
		const handleInsertShortcode = () => {
			if (selectedPersona === 'default') {
				return;
			}

			const shortcode = `[if_persona is="${selectedPersona}"]
Your ${personas[selectedPersona]} persona content goes here.
[/if_persona]`;

			// Insert into editor at current selection
			if (
				typeof wp !== 'undefined' &&
				wp.data &&
				wp.data.dispatch('core/block-editor')
			) {
				const { insertBlocks } = wp.data.dispatch('core/block-editor');
				const { createBlock } = wp.blocks;

				// Create a new custom HTML block with our shortcode
				const newBlock = createBlock('core/html', {
					content: shortcode,
				});

				insertBlocks(newBlock);
			}
		};

		return (
			<Fragment>
				<PanelBody
					title={__('Persona Content', 'cme-personas')}
					initialOpen={true}
				>
					<div className="persona-selector">
						<p>
							{__(
								'Use the [if_persona] shortcode to create persona-specific content.',
								'cme-personas'
							)}
						</p>

						<SelectControl
							label={__('Select Persona', 'cme-personas')}
							value={selectedPersona}
							options={personaOptions}
							onChange={setSelectedPersona}
							className="personas-list"
						/>

						<Button
							isPrimary
							disabled={selectedPersona === 'default'}
							onClick={handleInsertShortcode}
							className="insert-shortcode-button"
						>
							{__('Insert Shortcode', 'cme-personas')}
						</Button>

						<div className="shortcode-examples">
							<h3>{__('Shortcode Examples:', 'cme-personas')}</h3>
							<pre>
								{`[if_persona is="persona-id"]
  Content for specific persona
[/if_persona]

[if_persona is="persona-id,another-id"]
  Content for multiple personas
[/if_persona]

[if_persona not="persona-id"]
  Content for all except this persona
[/if_persona]`}
							</pre>
						</div>
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
