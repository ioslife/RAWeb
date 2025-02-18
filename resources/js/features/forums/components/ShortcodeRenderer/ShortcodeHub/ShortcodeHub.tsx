import { useAtom } from 'jotai';
import type { FC } from 'react';
import { useTranslation } from 'react-i18next';

import { GameAvatar } from '@/common/components/GameAvatar';
import { persistedHubsAtom } from '@/features/forums/state/forum.atoms';

interface ShortcodeHubProps {
  hubId: number;
}

export const ShortcodeHub: FC<ShortcodeHubProps> = ({ hubId }) => {
  const { t } = useTranslation();

  const [persistedHubs] = useAtom(persistedHubsAtom);

  const foundHub = persistedHubs?.find((hub) => hub.id === hubId);

  if (!foundHub) {
    return null;
  }

  return (
    <span data-testid="hub-embed" className="inline">
      <GameAvatar
        id={foundHub.gameId!}
        title={t('{{hubTitle}} (Hubs)', { hubTitle: foundHub.title })}
        badgeUrl={foundHub.badgeUrl!}
        size={24}
        variant="inline"
      />
    </span>
  );
};
