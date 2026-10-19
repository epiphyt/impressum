import { moveField } from '../mover';

describe( 'moveField', () => {
	const fields = [ 'a', 'b', 'c', 'd' ];

	it( 'moves a field before another field', () => {
		expect( moveField( fields, 'd', 'b', 'before' ) ).toEqual( [
			'a',
			'd',
			'b',
			'c',
		] );
	} );

	it( 'moves a field after another field', () => {
		expect( moveField( fields, 'a', 'c', 'after' ) ).toEqual( [
			'b',
			'c',
			'a',
			'd',
		] );
	} );

	it( 'does not mutate the given fields', () => {
		moveField( fields, 'a', 'd', 'after' );

		expect( fields ).toEqual( [ 'a', 'b', 'c', 'd' ] );
	} );

	it( 'keeps the order when moving a field next to itself', () => {
		expect( moveField( fields, 'b', 'b', 'after' ) ).toBe( fields );
	} );

	it( 'keeps the order for unknown fields', () => {
		expect( moveField( fields, 'x', 'b', 'before' ) ).toBe( fields );
		expect( moveField( fields, 'b', 'x', 'before' ) ).toBe( fields );
	} );
} );
