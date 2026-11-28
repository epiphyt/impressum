import { afterEach } from 'vitest';

// without globals, Testing Library cannot register its automatic cleanup
afterEach( async () => {
	if ( typeof document === 'undefined' ) {
		return;
	}

	const { cleanup } = await import( '@testing-library/react' );
	cleanup();
} );
