/**
 * External dependencies
 */
import { css, keyframes } from '@emotion/react';

export const navigatorProviderWrapper = css`
	position: relative;
	/* Prevents horizontal overflow while animating screen transitions */
	overflow-x: hidden;
	/* Mark this subsection of the DOM as isolated, providing performance benefits
	 * by limiting calculations of layout, style and paint to a DOM subtree rather
	 * than the entire page.
	 */
	contain: content;
`;

const fadeIn = keyframes( {
	from: {
		opacity: 0,
	},
} );

const fadeOut = keyframes( {
	to: {
		opacity: 0,
	},
} );

const slideFromRight = keyframes( {
	from: {
		transform: 'translateX(100px)',
	},
} );

export const slideToLeft = keyframes( {
	to: {
		transform: 'translateX(-80px)',
	},
} );

const slideFromLeft = keyframes( {
	from: {
		transform: 'translateX(-100px)',
	},
} );

export const slideToRight = keyframes( {
	to: {
		transform: 'translateX(80px)',
	},
} );

type NavigatorScreenAnimationProps = {
	skipInitialAnimation: boolean;
	direction: 'forwards' | 'backwards';
	isAnimatingOut: boolean;
};

const FADE = {
	DURATION: '70ms',
	EASING: 'linear',
	DELAY: {
		IN: '70ms',
		OUT: '40ms',
	},
};
const SLIDE = {
	DURATION: '300ms',
	EASING: 'cubic-bezier(0.33, 0, 0, 1)',
};

const ANIMATION = {
	forwards: {
		in: css`
			${ FADE.DURATION } ${ FADE.EASING } ${ FADE.DELAY
				.IN } both ${ fadeIn }, ${ SLIDE.DURATION } ${ SLIDE.EASING } both ${ slideFromRight }
		`,
		out: css`
			${ FADE.DURATION } ${ FADE.EASING } ${ FADE.DELAY
				.IN } both ${ fadeOut }, ${ SLIDE.DURATION } ${ SLIDE.EASING } both ${ slideToLeft }
		`,
	},
	backwards: {
		in: css`
			${ FADE.DURATION } ${ FADE.EASING } ${ FADE.DELAY
				.IN } both ${ fadeIn }, ${ SLIDE.DURATION } ${ SLIDE.EASING } both ${ slideFromLeft }
		`,
		out: css`
			${ FADE.DURATION } ${ FADE.EASING } ${ FADE.DELAY
				.OUT } both ${ fadeOut }, ${ SLIDE.DURATION } ${ SLIDE.EASING } both ${ slideToRight }
		`,
	},
};
const navigatorScreenAnimation = ( {
	direction,
	skipInitialAnimation,
	isAnimatingOut,
}: NavigatorScreenAnimationProps ) => {
	return css`
		position: ${ isAnimatingOut ? 'absolute' : 'relative' };
		z-index: ${ isAnimatingOut ? 0 : 1 };
		${ isAnimatingOut &&
		css`
			inset: 0;
		` }

		animation: ${ skipInitialAnimation
			? 'none'
			: ANIMATION[ direction ][ isAnimatingOut ? 'out' : 'in' ] };

		@media ( prefers-reduced-motion ) {
			animation: none;
		}
	`;
};

export const navigatorScreen = ( props: NavigatorScreenAnimationProps ) => css`
	/* Ensures horizontal overflow is visually accessible */
	overflow-x: auto;
	/* In case the root has a height, it should not be exceeded */
	max-height: 100%;

	${ navigatorScreenAnimation( props ) }
`;
