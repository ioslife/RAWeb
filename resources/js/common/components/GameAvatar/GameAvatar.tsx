import './GlowingImage.css';

import type { ComponentPropsWithoutRef, CSSProperties, FC, ImgHTMLAttributes } from 'react';

import { useCardTooltip } from '@/common/hooks/useCardTooltip';
import { usePageProps } from '@/common/hooks/usePageProps';
import type { BaseAvatarProps } from '@/common/models';
import { cn } from '@/common/utils/cn';

import { GameTitle } from '../GameTitle';
import { SystemChip } from '../SystemChip';

type GameAvatarProps = BaseAvatarProps &
  App.Platform.Data.Game & {
    decoding?: ImgHTMLAttributes<HTMLImageElement>['decoding'];
    gameTitleClassName?: string;
    loading?: ImgHTMLAttributes<HTMLImageElement>['loading'];
    shouldGlow?: boolean;
    showHoverCardProgressForUsername?: string;
    showSystemChip?: boolean;
    showSystemInTitle?: boolean;
    variant?: 'base' | 'inline';
    wrapperClassName?: string;
  };

export const GameAvatar: FC<GameAvatarProps> = ({
  badgeUrl,
  gameTitleClassName,
  id,
  showHoverCardProgressForUsername,
  system,
  title,
  wrapperClassName,
  decoding = 'async',
  loading = 'lazy',
  shouldGlow = false,
  showImage = true,
  showLabel = true,
  shouldLink = true,
  showSystemChip = false,
  showSystemInTitle = false,
  size = 32,
  hasTooltip = true,
  variant = 'base',
}) => {
  const { auth } = usePageProps();

  const { cardTooltipProps } = useCardTooltip({
    dynamicType: 'game',
    dynamicId: id,
    dynamicContext: showHoverCardProgressForUsername ?? auth?.user.displayName,
  });

  const Wrapper = shouldLink ? 'a' : 'div';

  const gameTitle = showSystemInTitle ? `${title} (${system?.name})` : title;

  return (
    <Wrapper
      href={shouldLink ? route('game.show', { game: id }) : undefined}
      className={cn(
        variant === 'base' ? 'flex max-w-fit items-center gap-2' : null,
        variant === 'inline' ? 'ml-0.5 mt-0.5 inline-block min-h-7 gap-2' : null,
        wrapperClassName,
      )}
      {...(hasTooltip && shouldLink ? cardTooltipProps : undefined)}
    >
      {showImage ? (
        <>
          {shouldGlow ? (
            <GlowingImage width={size} height={size} src={badgeUrl} alt={title ?? 'Game'} />
          ) : (
            <img
              loading={loading}
              decoding={decoding}
              width={size}
              height={size}
              src={badgeUrl}
              alt={title ?? 'Game'}
              className={cn('rounded-sm', variant === 'inline' ? 'mr-1.5' : null)}
            />
          )}
        </>
      ) : null}

      <div
        className={cn(
          variant === 'base' ? 'flex flex-col gap-0.5' : null,
          variant === 'inline' ? 'inline-block' : null,
        )}
      >
        {title && showLabel ? <GameTitle title={gameTitle} className={gameTitleClassName} /> : null}

        {system && showSystemChip ? (
          <SystemChip {...system} className="text-text hover:text-text" />
        ) : null}
      </div>
    </Wrapper>
  );
};

type GlowingImageProps = Pick<ComponentPropsWithoutRef<'img'>, 'src' | 'alt' | 'width' | 'height'>;

const GlowingImage: FC<GlowingImageProps> = ({ src, ...rest }) => {
  return (
    <div className="glowing-image-root" style={{ '--img-url': `url(${src})` } as CSSProperties}>
      <img src={src} className="glowing-image" loading="eager" decoding="sync" {...rest} />
    </div>
  );
};
