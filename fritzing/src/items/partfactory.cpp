/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-2012 Fachhochschule Potsdam - http://fh-potsdam.de

Fritzing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fritzing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fritzing.  If not, see <http://www.gnu.org/licenses/>.

********************************************************************

$Revision: 6595 $:
$Author: irascibl@gmail.com $:
$Date: 2012-10-23 12:25:28 +0200 (Tue, 23 Oct 2012) $

********************************************************************/

#include "partfactory.h"
#include "../viewgeometry.h"
#include "../model/modelpart.h"
#include "../model/modelbase.h"
#include "paletteitem.h"
#include "symbolpaletteitem.h"
#include "wire.h"
#include "virtualwire.h"
#include "tracewire.h"
#include "jumperitem.h"
#include "resizableboard.h"
#include "logoitem.h"
#include "schematicframe.h"
#include "resistor.h"
#include "moduleidnames.h"
#include "mysterypart.h"
#include "groundplane.h"
#include "note.h"
#include "ruler.h"
#include "dip.h"
#include "pinheader.h"
#include "screwterminal.h"
#include "hole.h"
#include "via.h"
#include "pad.h"
#include "capacitor.h"
#include "perfboard.h"
#include "breadboard.h"
#include "stripboard.h"
#include "led.h"
#include "../utils/folderutils.h"
#include "../utils/lockmanager.h"
#include "../utils/textutils.h"

static QString PartFactoryFolderPath;
static QHash<QString, LockedFile *> LockedFiles;

ItemBase * PartFactory::createPart( ModelPart * modelPart, ViewLayer::ViewLayerSpec viewLayerSpec, ViewLayer::ViewIdentifier viewIdentifier, const ViewGeometry & viewGeometry, long id, QMenu * itemMenu, QMenu * wireMenu, bool doLabel)
{
	modelPart->setModelIndexFromMultiplied(id);			// make sure the model index is synched with the id; this is not always the case when parts are first created.
	ItemBase * itemBase = createPartAux(modelPart, viewIdentifier, viewGeometry, id, itemMenu, wireMenu, doLabel);
	if (itemBase) {
		itemBase->setViewLayerSpec(viewLayerSpec);
	}
	return itemBase;
}

ItemBase * PartFactory::createPartAux( ModelPart * modelPart, ViewLayer::ViewIdentifier viewIdentifier, const ViewGeometry & viewGeometry, long id, QMenu * itemMenu, QMenu * wireMenu, bool doLabel)
{
	switch (modelPart->itemType()) {
		case ModelPart::Wire:
		{
			bool ratsnest = viewGeometry.getRatsnest();
			if (ratsnest) {
				return new VirtualWire(modelPart, viewIdentifier, viewGeometry, id, wireMenu);		
			}
			if (viewGeometry.getAnyTrace()) {
				TraceWire * traceWire = new TraceWire(modelPart, viewIdentifier, viewGeometry, id, wireMenu);
				return traceWire;
			}
			return new Wire(modelPart, viewIdentifier, viewGeometry, id, wireMenu, false);

		}
		case ModelPart::Note:
			return new Note(modelPart, viewIdentifier, viewGeometry, id, NULL);
		case ModelPart::CopperFill:
			return new GroundPlane(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::Jumper:
			return new JumperItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::ResizableBoard:
			return new ResizableBoard(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::Board:
			return new Board(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::Logo:
			if (modelPart->moduleID().startsWith("copper", Qt::CaseInsensitive)) {
				return new CopperLogoItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
			}
            else if (modelPart->moduleID().startsWith("Schematic", Qt::CaseInsensitive)) {
                return new SchematicLogoItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
            }
            else if (modelPart->moduleID().startsWith("Breadboard", Qt::CaseInsensitive)) {
                return new BreadboardLogoItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
            }
            // make sure "board" match comes after "breadboard" match
            else if (modelPart->moduleID().contains("board", Qt::CaseInsensitive)) {
                return new BoardLogoItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
            }
			return new LogoItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::Ruler:
			return new Ruler(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::Symbol:
			return new SymbolPaletteItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::Via:
			return new Via(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		case ModelPart::Hole:
			return new Hole(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
		default:
			{
				QString moduleID = modelPart->moduleID();
				if (moduleID.endsWith(ModuleIDNames::ModuleIDNameSuffix)) {
					if (moduleID.endsWith(ModuleIDNames::ResistorModuleIDName)) {
						return new Resistor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::CapacitorModuleIDName)) {
						return new Capacitor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::CrystalModuleIDName)) {
						return new Capacitor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::ThermistorModuleIDName)) {
						return new Capacitor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::ZenerDiodeModuleIDName)) {
						return new Capacitor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::PotentiometerModuleIDName)) {
						return new Capacitor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::InductorModuleIDName)) {
						return new Capacitor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::ColorLEDModuleIDName)) {
						return new LED(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::ColorFluxLEDModuleIDName)) {
						return new LED(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::LEDModuleIDName)) {
						return new Capacitor(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::PerfboardModuleIDName)) {
						return new Perfboard(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::StripboardModuleIDName)) {
						return new Stripboard(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::SchematicFrameModuleIDName)) {
						return new SchematicFrame(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::PadModuleIDName)) {
						return new Pad(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					if (moduleID.endsWith(ModuleIDNames::CopperBlockerModuleIDName)) {
						return new CopperBlocker(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}
					// must get the subclasses first
					if (modelPart->itemType() == ModelPart::Breadboard) {
						return new Breadboard(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
					}

				}
				QString family = modelPart->properties().value("family", "");
				if (family.compare("mystery part", Qt::CaseInsensitive) == 0) {
					return new MysteryPart(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
				}
				if (family.compare("screw terminal", Qt::CaseInsensitive) == 0) {
					return new ScrewTerminal(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
				}
				if (family.compare("pin header", Qt::CaseInsensitive) == 0) {
					return new PinHeader(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
				}
				if (family.compare("generic IC", Qt::CaseInsensitive) == 0) {
					return new Dip(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);
				}
				return new PaletteItem(modelPart, viewIdentifier, viewGeometry, id, itemMenu, doLabel);

			}
	}
}

QString PartFactory::getSvgFilename(ModelPart * modelPart, const QString & expectedFileName) {

	if (expectedFileName.startsWith("pcb/dip_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &MysteryPart::makePcbDipSvg);
	}

	if (expectedFileName.startsWith("pcb/mystery_part_", Qt::CaseInsensitive)) {
        if (expectedFileName.contains("dip")) {
		    return getSvgFilenameAux(expectedFileName, modelPart, &MysteryPart::makePcbDipSvg);
        }
        return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);
	}

	if (expectedFileName.startsWith("breadboard/mystery_part_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &MysteryPart::makeBreadboardSvg);
	}

	if (expectedFileName.startsWith("breadboard/screw_terminal_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &ScrewTerminal::makeBreadboardSvg);
	}

	if (expectedFileName.startsWith("schematic/screw_terminal_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &ScrewTerminal::makeSchematicSvg);
	}

	if (expectedFileName.startsWith("pcb/screw_terminal_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &ScrewTerminal::makePcbSvg);
	}

	if (expectedFileName.startsWith("breadboard/generic_sip_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &Dip::makeBreadboardSvg);
	}

	if (expectedFileName.startsWith("schematic/mystery_part_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &MysteryPart::makeSchematicSvg);
	}

	if (expectedFileName.startsWith("schematic/generic_sip_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &MysteryPart::makeSchematicSvg);
	}

	if (expectedFileName.startsWith("pcb/generic_sip_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);
	}

	if (expectedFileName.startsWith("schematic/generic_ic_dip_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &Dip::makeSchematicSvg);
	}

	if (expectedFileName.startsWith("breadboard/generic_ic_dip_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &Dip::makeBreadboardSvg);
	}

	if (expectedFileName.startsWith("pcb/generic_ic_dip_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &MysteryPart::makePcbDipSvg);
	}

	if (expectedFileName.startsWith("pcb/nsjumper_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);
	}

	if (expectedFileName.startsWith("pcb/jumper_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);
	}

	if (expectedFileName.startsWith("pcb/shrouded_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);
	}

	if (expectedFileName.startsWith("pcb/molex_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);
	}

	if (expectedFileName.startsWith("pcb/longpad_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);
	}

	if (expectedFileName.startsWith("bread/molex_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makeBreadboardSvg);
	}

	if (expectedFileName.startsWith("bread/longpad_", Qt::CaseInsensitive)) {
		return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makeBreadboardSvg);
	}

	if (expectedFileName.contains("pin_header", Qt::CaseInsensitive)) {
		if (expectedFileName.contains("schematic", Qt::CaseInsensitive)) {
			return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makeSchematicSvg);
		}
		else if (expectedFileName.contains("bread", Qt::CaseInsensitive)) {
			return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makeBreadboardSvg);		
		}
		else if (expectedFileName.contains("pcb", Qt::CaseInsensitive)) {
			return getSvgFilenameAux(expectedFileName, modelPart, &PinHeader::makePcbSvg);		
		}
	}

	if (expectedFileName.contains("perfboard", Qt::CaseInsensitive) || expectedFileName.contains("stripboard", Qt::CaseInsensitive)) {
		if (expectedFileName.contains("icon")) return expectedFileName;

		return getSvgFilenameAux(expectedFileName, modelPart, &Perfboard::makeBreadboardSvg);		
	}

	return "";
}

bool PartFactory::svgFileExists(const QString & expectedFileName, QString & path) {
	QString p = FolderUtils::getApplicationSubFolderPath("parts") + "/"+ ItemBase::SvgFilesDir + "/core/";
	if (QFileInfo(p + expectedFileName).exists()) {
        path = expectedFileName;
        return true;
    }

	path = partPath() + expectedFileName;
	QFileInfo info(path);
	return info.exists();
}

QString PartFactory::getSvgFilenameAux(const QString & expectedFileName, const ModelPart * modelPart, GenSvg genSvg)
{
    Q_UNUSED(modelPart);

    QString path;
    if (svgFileExists(expectedFileName, path)) return path;

	QString svg = (*genSvg)(expectedFileName);
	if (TextUtils::writeUtf8(path, svg)) {
		return path;
	}

	return "";
}


bool PartFactory::fzpFileExists(const QString & moduleID, QString & path) {
    QString expectedFileName = moduleID + FritzingPartExtension;
	path = FolderUtils::getApplicationSubFolderPath("parts") + "/core/" + expectedFileName;
	if (QFileInfo(path).exists()) {
        path = expectedFileName;
        return true;
    }

	path = fzpPath() + expectedFileName;
	QFileInfo info(path);
	return info.exists();
}

QString PartFactory::getFzpFilenameAux(const QString & moduleID, QString (*getFzp)(const QString &))
{
	QString path;
    if (fzpFileExists(moduleID, path)) return path;

	QString fzp = (*getFzp)(moduleID);
	if (TextUtils::writeUtf8(path, fzp)) {
		return path;
	}

	return "";
}


QString PartFactory::getFzpFilename(const QString & moduleID) 
{
    QString filename = fzpPath() + moduleID + ".fzp";
    QFileInfo info(filename);
    if (info.exists()) return filename;

	if (moduleID.endsWith(ModuleIDNames::PerfboardModuleIDName)) {
		return getFzpFilenameAux(moduleID, &Perfboard::genFZP);
	}

	if (moduleID.endsWith(ModuleIDNames::StripboardModuleIDName)) {
		return getFzpFilenameAux(moduleID, &Stripboard::genFZP);
	}

	if (moduleID.startsWith("generic_ic_dip")) {
		return getFzpFilenameAux(moduleID, &Dip::genDipFZP);
	}

	if (moduleID.startsWith("screw_terminal")) {
		return getFzpFilenameAux(moduleID, &ScrewTerminal::genFZP);
	}

	if (moduleID.startsWith("generic_sip")) {
		return getFzpFilenameAux(moduleID, &Dip::genSipFZP);
	}

	if (moduleID.contains("_pin_header_") && moduleID.startsWith("generic_")) {
		return getFzpFilenameAux(moduleID, &PinHeader::genFZP);
	}

	if (moduleID.startsWith("mystery_part")) {
		if (moduleID.contains("dip", Qt::CaseInsensitive)) {
			return getFzpFilenameAux(moduleID, &MysteryPart::genDipFZP);
		}
		else {
			return getFzpFilenameAux(moduleID, &MysteryPart::genSipFZP);
		}
	}

	return "";
}

void PartFactory::initFolder()
{
	LockManager::initLockedFiles("partfactory", PartFactoryFolderPath, LockedFiles, LockManager::SlowTime);
	QFileInfoList backupList;
	LockManager::checkLockedFiles("partfactory", backupList, LockedFiles, true, LockManager::SlowTime);
	FolderUtils::makePartFolderHierarchy(PartFactoryFolderPath, "core");
	FolderUtils::makePartFolderHierarchy(PartFactoryFolderPath, "contrib");
}

void PartFactory::cleanup()
{
	LockManager::releaseLockedFiles(PartFactoryFolderPath, LockedFiles);
}

ModelPart * PartFactory::fixObsoleteModuleID(QDomDocument & domDocument, QDomElement & instance, QString & moduleIDRef, ModelBase * referenceModel) {
	// TODO: less hard-coding
	if (moduleIDRef.startsWith("generic_male")) {
		ModelPart * modelPart = referenceModel->retrieveModelPart(moduleIDRef);
		if (modelPart != NULL) {
			instance.setAttribute("moduleIdRef", moduleIDRef);
			QDomElement prop = domDocument.createElement("property");
			instance.appendChild(prop);
			prop.setAttribute("name", "form");
			prop.setAttribute("value", PinHeader::MaleFormString);
			return modelPart;
		}
	}

	if (moduleIDRef.startsWith("generic_rounded_female")) {
		ModelPart * modelPart = referenceModel->retrieveModelPart(moduleIDRef);
		if (modelPart != NULL) {
			instance.setAttribute("moduleIdRef", moduleIDRef);
			QDomElement prop = domDocument.createElement("property");
			instance.appendChild(prop);
			prop.setAttribute("name", "form");
			prop.setAttribute("value", PinHeader::FemaleRoundedFormString);
			return modelPart;
		}
	}

	return NULL;
}

QString PartFactory::folderPath() {
	return PartFactoryFolderPath;
}

QString PartFactory::fzpPath() {
    return PartFactoryFolderPath + "/core/";
}

QString PartFactory::partPath() {
    return PartFactoryFolderPath + "/svg/core/";
}

QString PartFactory::makeSipOrDipOr(const QStringList & labels, bool hasLayout, bool sip) 
{		
	if (hasLayout) {
		return MysteryPart::makeSchematicSvg(labels, false);
	}
		
    if (sip) {
		return MysteryPart::makeSchematicSvg(labels, true);
	}

	return Dip::makeSchematicSvg(labels);
}

